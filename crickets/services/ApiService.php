<?php
include 'API_BASE.php';

class ApiService
{
    private static function request(string $endpoint, array $params = [])
    {
        $params['APIkey'] = API_KEY; 
        $url = rtrim(BASE_API_URL, '/') . '/' . ltrim($endpoint, '/');
        $url .= '?' . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 5
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

        $data = json_decode($response, true);

        // Optional: return empty 'result' if missing
        if (!isset($data['result'])) {
            $data['result'] = [];
        }

        return $data;
    }

    public static function getStanding(array $params = [])
    {
        return self::request('', array_merge(['method' => 'get_standings'], $params));
    }

    // 🔹 Fetch events (upcoming / date range)
    public static function getMatchEvent(array $params = [])
    {
        return self::request('', array_merge(['method' => 'get_events'], $params));
    }

    // 🔹 Fetch live matches
    public static function getLivescores(array $params = [])
    {
        return self::request('', array_merge(['method' => 'get_livescore'], $params));
    }

    public static function getMatchInfo(array $params = [])
    {
        return self::request('', array_merge(['method' => 'get_livescore'], $params));
    }
    public static function getSeries(array $params = [])
    {
        return self::request('/series', $params);
    }

    public static function getLiveMatches(array $params = [])
    {
        return self::request('/currentMatches', $params);
    }

    public static function getLivescore(array $params = [])
    {
        return self::request('/cricScore', $params);
    }

    public static function getCurrentMatch(array $params = [])
    {
        return self::request('/currentMatches', $params);
    }

    public static function getSeriesInfo(array $params = [])
    {
        return self::request('/series_info', $params);
    }


    public static function getMatchSqoud(array $params = [])
    {
        return self::request('/match_squad', $params);
    }


    public static function getAllPlayer (array $params = []){

        return self::request('/players', $params);
    }

    public static function getUpComingMatch(array $params = [])
    {

        return self::request('/cricScore', $params);
    }

    public static function getMatchInfoWthScoreCard(string $matchId)
    {
        return self::request('/match_scorecard', [
            'id' => $matchId
        ]);
    }


    

    public static function getMatchPoints(string $matchId)
    {
        return self::request('/match_points', [
            'id' => $matchId
        ]);
    }


    public static function getSeriesPoints(string $seriesId)
    {
        return self::request('/series_points', [
            'id' => $seriesId
        ]);
    }

    public static function getMatchSquad(string $matchId)
    {
        return self::request('/match_squad', [
            'id' => $matchId
        ]);
    }


    public static function getUpcomingInfo(string $matchId)
    {
        return self::request('/match_info', [
            'id' => $matchId
        ]);
    }
}
