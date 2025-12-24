
<?php
class Post {
    public $db;

    public function __construct() {
        $this->db = dbConn(); 
    }

    public function replacePostNoForCreate($postNo)
    {
        try {
            $this->db->beginTransaction();

            // get max postNo
            $stmt = $this->db->query("SELECT IFNULL(MAX(postNo), 0) + 1 AS nextNo FROM post");
            $nextNo = (int)$stmt->fetch(PDO::FETCH_ASSOC)['nextNo'];

            // move existing post
            $update = $this->db->prepare(
                "UPDATE post SET postNo = :nextNo WHERE postNo = :postNo"
            );
            $update->execute([
                'nextNo' => $nextNo,
                'postNo' => $postNo
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }


    // Check if postNo exists (exclude current post)
    public function findPostByPostNo($postNo, $excludeId = null)
    {
        $sql = "SELECT id FROM post WHERE postNo = ?";
        $params = [$postNo];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get next available postNo
    public function getNextPostNo()
    {
        $stmt = $this->db->query("SELECT MAX(postNo) AS max_no FROM post");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row['max_no'] ?? 0) + 1;
    }

    // Replace postNo logic
    public function replacePostNo($currentId, $newPostNo)
    {
        $this->db->beginTransaction();

        try {
            // Find conflict post
            $conflict = $this->findPostByPostNo($newPostNo, $currentId);

            if ($conflict) {
                $newFreeNo = $this->getNextPostNo();

                // Move old post to new number
                $stmt = $this->db->prepare("UPDATE post SET postNo = ? WHERE id = ?");
                $stmt->execute([$newFreeNo, $conflict['id']]);
            }

            // Update current post
            $stmt = $this->db->prepare("UPDATE post SET postNo = ? WHERE id = ?");
            $stmt->execute([$newPostNo, $currentId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }


    public function updatePostNo(int $id, int $postNo): bool
    {
        $sql = "UPDATE post SET postNo = :postNo WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':postNo' => $postNo,
            ':id' => $id
        ]);
    }
    public function getCategories()
    {
        $stmt = $this->db->prepare("SELECT id, name FROM categories ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function postNoExists(int $postNo, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM post WHERE postNo = :postNo";

        if ($excludeId !== null) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':postNo', $postNo, PDO::PARAM_INT);

        if ($excludeId !== null) {
            $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function togglePostStatus($id)
    {
        $sql = "UPDATE post
            SET status = IF(status = 1, 0, 1)
            WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
    public function getLastPosts($limit = 5, $lang = 'en')
    {
        // Validate language
        $lang = in_array($lang, ['en', 'bn']) ? $lang : 'en';

        // Select language-specific fields
        $name_field = $lang === 'en' ? 'name' : 'name_bn';
        $description_field = $lang === 'en' ? 'description' : 'description_bn';
        $meta_text_field = $lang === 'en' ? 'meta_text' : 'meta_text_bn';

        $query = "SELECT p.id, p.slug, p.$name_field AS name, p.image, p.image_mb,
                     p.$description_field AS description, 
                     p.game_link, p.category_id, p.created_at, 
                     p.$meta_text_field AS meta_text, 
                     c.name AS category_name,
                     p.post_by, p.status, p.postNo, p.meta_title, p.game_link
              FROM post p
              JOIN categories c ON p.category_id = c.id WHERE status = 1
              ORDER BY p.postNo ASC
              LIMIT :limit";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching last posts: " . $e->getMessage());
            return [];
        }
    }
    // Get total post count by category
    public function getPostCountByCategory($categoryId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM post WHERE category_id = ?");
        $stmt->execute([$categoryId]);
        return $stmt->fetchColumn();
    }

    // Get paginated posts by category

    public function getPostByCategory($categoryId, $lang = 'en', $limit = 10, $page = 1)
    {
        // Validate language
        $lang = in_array($lang, ['en', 'bn']) ? $lang : 'en';

        // Select language-specific fields
        $name_field = $lang === 'en' ? 'name' : 'name_bn';
        $description_field = $lang === 'en' ? 'description' : 'description_bn';
        $meta_text_field = $lang === 'en' ? 'meta_text' : 'meta_text_bn';
        $meta_desc_field = $lang === 'en' ? 'meta_desc' : 'meta_desc_bn';
        $meta_keyword_field = $lang === 'en' ? 'meta_keyword' : 'meta_keyword_bn';
                $offset = ($page - 1) * $limit;
                $limitClause = '';
                if ($limit !== null && (int)$limit > 0) {
                        $limitClause = 'LIMIT :limit OFFSET :offset';
                }
                $query = "SELECT 
                p.id, 
                p.slug, 
                p.$name_field AS name, 
                p.image,
                p.image_mb,
                p.$description_field AS description, 
                p.$meta_desc_field AS meta_desc, 
                p.$meta_keyword_field AS meta_keyword, 
                p.game_link, 
                p.category_id, 
                p.created_at, 
                p.$meta_text_field AS meta_text, 
                c.name AS category_name,
                p.post_by, p.game_link
              FROM post p
              JOIN categories c ON p.category_id = c.id 
              WHERE p.category_id = :categoryId AND p.status = 1
              ORDER BY p.postNo ASC $limitClause";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':categoryId', $categoryId, PDO::PARAM_INT);
            if ($limit !== null && (int)$limit > 0) {
                $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching posts by category: " . $e->getMessage());
            return [];
        }
    }


    public function getPost($lang = 'en')
    {
        // Validate language
        $lang = in_array($lang, ['en', 'bn']) ? $lang : 'en';

        // Select language-specific fields
        $name_field = $lang === 'en' ? 'name' : 'name_bn';
        $description_field = $lang === 'en' ? 'description' : 'description_bn';
        $meta_text_field = $lang === 'en' ? 'meta_text' : 'meta_text_bn';
        $meta_desc_field = $lang === 'en' ? 'meta_desc' : 'meta_desc_bn';
        $meta_keyword_field = $lang === 'en' ? 'meta_keyword' : 'meta_keyword_bn';
        $query = "SELECT p.id, p.slug, p.$name_field AS name, p.image, p.image_mb, 
                     p.$description_field AS description, p.$description_field AS description, p.$meta_desc_field AS meta_desc,
                     p.game_link, p.category_id, p.created_at, 
                     p.$meta_text_field AS meta_text,
                    p.post_by, p.status, p.postNo, p.meta_title, p.game_link,
                     c.name AS category_name 
              FROM post p
              JOIN categories c ON p.category_id = c.id 
              ORDER BY p.postNo ASC";

        try {
            $stmt = $this->db->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching games: " . $e->getMessage());
            return [];
        }
    }
    public function getPostAll($lang = 'en')
    {
        // Validate language
        $lang = in_array($lang, ['en', 'bn']) ? $lang : 'en';

        // Select language-specific fields
        $name_field = $lang === 'en' ? 'name' : 'name_bn';
        $description_field = $lang === 'en' ? 'description' : 'description_bn';
        $meta_text_field = $lang === 'en' ? 'meta_text' : 'meta_text_bn';
        $meta_desc_field = $lang === 'en' ? 'meta_desc' : 'meta_desc_bn';
        $meta_keyword_field = $lang === 'en' ? 'meta_keyword' : 'meta_keyword_bn';
        $query = "SELECT p.id, p.slug, p.$name_field AS name, p.image, p.image_mb, 
                     p.$description_field AS description, p.$description_field AS description, p.$meta_desc_field AS meta_desc,
                     p.game_link, p.category_id, p.created_at, 
                     p.$meta_text_field AS meta_text,
                    p.post_by, p.status, p.postNo, p.meta_title,
                     c.name AS category_name 
              FROM post p
            JOIN categories c ON p.category_id = c.id WHERE p.status = 1
            ORDER BY p.postNo ASC";

        try {
            $stmt = $this->db->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching games: " . $e->getMessage());
            return [];
        }
    }

    public function getPostById($id, $lang = 'en') {
        // Validate language
        $lang = in_array($lang, ['en', 'bn']) ? $lang : 'en';
        // Select language-specific fields
        $name_field = $lang === 'en' ? 'name' : 'name_bn';
        $description_field = $lang === 'en' ? 'description' : 'description_bn';
        $meta_text_field = $lang === 'en' ? 'meta_text' : 'meta_text_bn';
        $query = "SELECT 
            p.id, 
            p.$name_field AS name, 
            p.image,
            p.image_mb,
            p.$description_field AS description, 
            p.meta_desc, 
            p.meta_keyword,
            p.meta_desc_bn, 
            p.meta_keyword_bn,
            p.game_link, 
            p.category_id, 
            p.created_at,
            p.post_by,
            p.status,
            p.meta_title,
            p.postNo,
            p.$meta_text_field AS meta_text, 
            c.name AS category_name 
          FROM post p
          JOIN categories c ON p.category_id = c.id 
          WHERE p.id = :id 
          LIMIT 1";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching game by ID: " . $e->getMessage());
            return false;
        }
    }
    public function getPostBySlug($slug, $lang = 'en')
    {
        // Validate language
        $lang = in_array($lang, ['en', 'bn']) ? $lang : 'en';

        // Select language-specific fields
        $name_field = $lang === 'en' ? 'name' : 'name_bn';
        $description_field = $lang === 'en' ? 'description' : 'description_bn';
        $meta_text_field = $lang === 'en' ? 'meta_text' : 'meta_text_bn';
        $meta_desc_field = $lang === 'en' ? 'meta_desc' : 'meta_desc_bn';
        $meta_keyword_field = $lang === 'en' ? 'meta_keyword' : 'meta_keyword_bn';
        $query = "SELECT p.id, p.$name_field AS name, p.image, p.image_mb, p.$description_field AS description, p.$meta_desc_field AS meta_desc, 
            p.$meta_keyword_field AS meta_keyword,
                     p.game_link, p.category_id, p.created_at, p.$meta_text_field AS meta_text, 
                     c.name AS category_name,
                    p.post_by,
                    p.status,
                    p.meta_title,
                    p.postNo,
                    p.slug
              FROM post p
              JOIN categories c ON p.category_id = c.id 
              WHERE p.slug = :slug 
              LIMIT 1";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching game by Slug: " . $e->getMessage());
            return false;
        }
    }

    public function getRelatedpost($gameId, $categoryId, $limit = 4, $lang = 'en')
    {
        // Validate language
        $lang = in_array($lang, ['en', 'bn']) ? $lang : 'en';
        // Select language-specific fields
        $name_field = $lang === 'en' ? 'name' : 'name_bn';
        $description_field = $lang === 'en' ? 'description' : 'description_bn';
        $meta_text_field = $lang === 'en' ? 'meta_text' : 'meta_text_bn';
        $query = "SELECT p.id, p.slug, p.$name_field AS name, p.image, p.image_mb,
                     p.$description_field AS description, 
                     p.game_link, p.category_id, p.created_at, 
                     p.$meta_text_field AS meta_text, 
                     c.name AS category_name,
                      p.post_by, p.status, p.postNo, p.meta_title, p.game_link
              FROM post p
              JOIN categories c ON p.category_id = c.id 
              WHERE p.id != :id AND p.category_id = :category_id AND p.status = 1
              ORDER BY RAND() 
              LIMIT :limit";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $gameId, PDO::PARAM_INT);
            $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching related post: " . $e->getMessage());
            return [];
        }
    }

    public function getPopularpost($limit = 6, $lang = 'en')
    {
        // Validate language
        $lang = in_array($lang, ['en', 'bn']) ? $lang : 'en';
        // Select language-specific fields
        $name_field = $lang === 'en' ? 'name' : 'name_bn';
        $description_field = $lang === 'en' ? 'description' : 'description_bn';
        $meta_text_field = $lang === 'en' ? 'meta_text' : 'meta_text_bn';

        $query = "SELECT p.id, p.$name_field AS name, p.image, p.$description_field AS description, 
                     p.game_link, p.category_id, p.created_at, p.$meta_text_field AS meta_text, 
                     p.slug, 
                     c.name AS category_name,  
                     p.post_by, p.status, p.postNo, p.meta_title, p.game_link
              FROM post p
              JOIN categories c ON p.category_id = c.id WHERE p.status = 1
              ORDER BY p.postNo ASC
              LIMIT :limit";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching popular post: " . $e->getMessage());
            return [];
        }
    }


    public function searchpost($query, $lang = 'en') {
        // Validate language
        $lang = in_array($lang, ['en', 'bn']) ? $lang : 'en';
        
        // Select language-specific fields
        $name_field = $lang === 'en' ? 'name' : 'name_bn';
        $description_field = $lang === 'en' ? 'description' : 'description_bn';
        $meta_text_field = $lang === 'en' ? 'meta_text' : 'meta_text_bn';

        $sql = "SELECT p.id, p.$name_field AS name, p.image, p.$description_field AS description, 
                       p.game_link, p.category_id, p.created_at, p.$meta_text_field AS meta_text, 
                       c.name AS category_name,
                        p.post_by
                FROM post p
                JOIN categories c ON p.category_id = c.id
                WHERE p.$name_field LIKE :query OR c.name LIKE :query AND p.status = 1";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['query' => '%' . $query . '%']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error searching post: " . $e->getMessage());
            return [];
        }
    }
    // Utility: Generate slug from text
    private function generateSlug($text)
    {
        // Convert to lowercase, remove special chars, replace spaces with hyphens
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }

    public function createpost($name, $image, $image_mb, $description, $link, $category_id, $meta_text, $name_bn, $description_bn, $meta_text_bn, $meta_desc, $meta_keyword, $meta_desc_bn, $meta_keyword_bn, $post_by, $status, $postNo,$meta_title, $slug = null)
    {
        // Auto-generate slug from English name if not provided
        $slug = $slug ?: $this->generateSlug($name);

        $data = [
            'name' => $name,
            'slug' => $slug,
            'image' => $image,
            'image_mb' => $image_mb,
            'description' => $description,
            'game_link' => $link,
            'category_id' => $category_id,
            'meta_text' => $meta_text,
            'name_bn' => $name_bn,
            'description_bn' => $description_bn,
            'meta_text_bn' => $meta_text_bn,
            'meta_desc' => $meta_desc,
            'meta_keyword' => $meta_keyword,
            'meta_desc_bn' => $meta_desc_bn,
            'meta_keyword_bn' => $meta_keyword_bn,
            'post_by' => $post_by,
            'status' => $status,
            'postNo' => $postNo,
            'meta_title' => $meta_title
        ];
        return dbInsert('post', $data);
    }

    public function updatePost($id, $name, $image, $image_mb, $description, $game_link, $category_id, $meta_text, $name_bn, $description_bn, $meta_text_bn, $meta_desc, $meta_keyword, $meta_desc_bn, $meta_keyword_bn, $post_by, $status, $postNo, $meta_title, $slug = null)
    {
        if (!$this->getPostById($id)) {
            return false;
        }
        $slug = $slug ?: $this->generateSlug($name);
        $data = [
            'name' => $name,
            'slug' => $slug,
            'image' => $image,
            'image_mb' => $image_mb,
            'description' => $description,
            'game_link' => $game_link,
            'category_id' => $category_id,
            'meta_text' => $meta_text,
            'name_bn' => $name_bn,
            'description_bn' => $description_bn,
            'meta_text_bn' => $meta_text_bn,
            'meta_desc' => $meta_desc,
            'meta_keyword' => $meta_keyword,
            'meta_desc_bn' => $meta_desc_bn,
            'meta_keyword_bn' => $meta_keyword_bn,
            'post_by' => $post_by,
            'status' => $status,
            'postNo' => $postNo,
            'meta_title' => $meta_title

        ];
        return dbUpdate('post', $data, "id=" . $this->db->quote($id));
    }

    // Delete a product (unchanged)
    public function deletePost($id) {
        return dbDelete('post', "id=" . $this->db->quote($id));
    }

    // Get total count of posts
    public function getPostCount()
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM post");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getPostPaginated($limit, $offset)
    {
        $stmt = $this->db->prepare("SELECT * FROM post ORDER BY postNo ACS LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>