
<?php

function dbConn()
{
   $servername = "localhost";
    $username = "root";
    $password = "";
    $db = "fw_db";
  
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$db", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

function dbClose(&$conn)
{
    $conn = null;
}




// Helper: sanitize table identifier (allow schema.table)
function sanitizeIdentifier(string $ident): string
{
    // Keep letters, numbers, underscore and dot
    $ident = preg_replace('/[^A-Za-z0-9_\.]/', '', $ident);
    // Prevent empty table name
    if ($ident === '') throw new InvalidArgumentException("Invalid identifier");
    return $ident;
}

// Helper: sanitize columns list (allow '*' or col or col AS alias)
function sanitizeColumnList(string $cols): string
{
    $cols = trim($cols);
    if ($cols === '*') return '*';

    $parts = explode(',', $cols);
    $clean = [];
    foreach ($parts as $p) {
        $p = trim($p);
        // Allow forms like `col`, `table.col`, or `col AS alias`
        if (preg_match('/^([A-Za-z0-9_\.]+)(\s+(?:AS\s+)?([A-Za-z0-9_]+))?$/i', $p, $m)) {
            $col = sanitizeIdentifier($m[1]);
            if (!empty($m[3])) {
                $alias = preg_replace('/[^A-Za-z0-9_]/', '', $m[3]);
                $clean[] = "$col AS $alias";
            } else {
                $clean[] = $col;
            }
        } else {
            // If it doesn't match the safe pattern, reject
            throw new InvalidArgumentException("Invalid column expression: $p");
        }
    }
    return implode(', ', $clean);
}

/**
 * Secure dbSelect
 * $params is an associative array of placeholder => value (e.g. [':username' => $username])
 */
function dbSelect($table, $columns = "*", $criteria = "", $params = [], $clause = "")
{
    if (empty($table)) return false;
    try {
        $table = sanitizeIdentifier($table);
        $columns = sanitizeColumnList($columns);

        $sql = "SELECT $columns FROM $table";
        if (!empty($criteria)) $sql .= " WHERE $criteria";
        if (!empty($clause)) $sql .= " $clause";

        $conn = dbConn();
        // ensure exceptions on error
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        dbClose($conn);
        return $result;
    } catch (PDOException $e) {
        error_log("DB select error: " . $e->getMessage() . " SQL: " . ($sql ?? ''));
        return false;
    } catch (Exception $e) {
        error_log("DB select error: " . $e->getMessage());
        return false;
    }
}

/**
 * Secure dbInsert
 * $data is associative array field => value
 */
function dbInsert($table, $data = [])
{
    if (empty($table) || empty($data)) return false;
    try {
        $table = sanitizeIdentifier($table);

        // sanitize field names and build placeholders
        $fields = [];
        $placeholders = [];
        $params = [];
        foreach ($data as $k => $v) {
            $field = preg_replace('/[^A-Za-z0-9_\.]/', '', $k);
            if ($field === '') throw new InvalidArgumentException("Invalid field name: $k");
            $fields[] = $field;
            $ph = ':' . $field;
            $placeholders[] = $ph;
            $params[$ph] = $v;
        }

        $fieldList = implode(", ", $fields);
        $phList = implode(", ", $placeholders);
        $sql = "INSERT INTO $table ($fieldList) VALUES ($phList)";

        $conn = dbConn();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        dbClose($conn);
        return true;
    } catch (PDOException $e) {
        error_log("DB insert error: " . $e->getMessage() . " SQL: " . ($sql ?? ''));
        return false;
    } catch (Exception $e) {
        error_log("DB insert error: " . $e->getMessage());
        return false;
    }
}

/**
 * Secure dbUpdate
 * $data: associative field => value
 * $criteria: SQL WHERE clause with placeholders (e.g. "id = :id")
 * $params: additional params for WHERE clause (associative placeholder => value)
 */
function dbUpdate($table, $data = [], $criteria = "", $params = [])
{
    if (empty($table) || empty($data) || empty($criteria)) return false;
    try {
        $table = sanitizeIdentifier($table);

        $setParts = [];
        $setParams = [];
        foreach ($data as $k => $v) {
            $field = preg_replace('/[^A-Za-z0-9_\.]/', '', $k);
            if ($field === '') throw new InvalidArgumentException("Invalid field name: $k");
            $ph = ':set_' . $field;            // avoid collisions
            $setParts[] = "$field = $ph";
            $setParams[$ph] = $v;
        }
        $setClause = implode(", ", $setParts);

        $sql = "UPDATE $table SET $setClause WHERE $criteria";

        $conn = dbConn();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare($sql);

        // merge parameters (SET params first, then where params)
        $allParams = array_merge($setParams, $params);
        $stmt->execute($allParams);
        dbClose($conn);
        return true;
    } catch (PDOException $e) {
        error_log("DB update error: " . $e->getMessage() . " SQL: " . ($sql ?? ''));
        return false;
    } catch (Exception $e) {
        error_log("DB update error: " . $e->getMessage());
        return false;
    }
}

/**
 * Secure dbDelete
 * $criteria: SQL WHERE clause with placeholders (e.g. "id = :id")
 * $params: associative placeholder => value
 */
function dbDelete($table, $criteria, $params = [])
{
    if (empty($table) || empty($criteria)) return false;
    try {
        $table = sanitizeIdentifier($table);
        $sql = "DELETE FROM $table WHERE $criteria";

        $conn = dbConn();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        dbClose($conn);
        return true;
    } catch (PDOException $e) {
        error_log("DB delete error: " . $e->getMessage() . " SQL: " . ($sql ?? ''));
        return false;
    } catch (Exception $e) {
        error_log("DB delete error: " . $e->getMessage());
        return false;
    }
}



function dbCount($table = "", $criteria = "")
{
    if (empty($table)) return false;

    $sql = "SELECT COUNT(*) as count FROM $table";
    if (!empty($criteria)) $sql .= " WHERE $criteria";

    $conn = dbConn();
    try {
        $stmt = $conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        dbClose($conn);
        return $result['count'];
    } catch (PDOException $e) {
        echo "Error counting data: " . $e->getMessage();
        return false;
    }
}
