<?php
include 'API_BASE.php';

class ApiService
{
    private static function request($endpoint, array $params = [])
    {
        $params['apikey'] = API_KEY;

        $url = BASE_API_URL . $endpoint . '?' . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return [
                'error' => true,
                'message' => curl_error($ch),
            ];
        }

        curl_close($ch);
        return json_decode($response, true);
    }

    // 🔹 Global functions (with params support)
    public static function getSeries(array $params = [])
    {
        return self::request('/series', $params);
    }

    public static function getLiveMatches(array $params = [])
    {
        return self::request('/currentMatches', $params);
    }


    public static function getMatchDetails($matchId, array $params = [])
    {
        $params['id'] = $matchId;
        return self::request('/match', $params);
    }


    public static function getMatchScoreCard(array $params = [])
    {
        return self::request('/match_scorecard', $params);
    }

    public static function getCurrentMatch(array $params = [])
    {
        return self::request('/currentMatches', $params);
    }

    public static function getSeriesInfo(array $params = [])
    {
        return self::request('/series_info', $params);
    }



    public static function getAllSeries(array $params = [])
    {
        return self::request('/series', $params);
    }


    public static function getMatchSqoud(array $params = [])
    {
        return self::request('/match_squad', $params);
    }



    public static function getAll(array $params = [])
    {
        return self::request('/match', $params);
    }

    public static function getAllPlayer (array $params = []){

        return self::request('/players', $params);
    }

    public static function getUpComingMatch(array $params = [])
    {

        return self::request('/cricScore', $params);
    }

    public static function getFinishedMatch(array $params = [])
    {
        return self::request('/finisMatch', $params);
    }

}
