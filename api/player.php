<?php
/**
 * PHP Stream Extractor & Player
 * Combines Scraping, Token Extraction, and Video.js
 */

error_reporting(0);
require_once 'channels.php';

// --- INPUT HANDLING ---
$id = isset($_GET['id']) ? $_GET['id'] : 'star1in';
$action = isset($_GET['action']) ? $_GET['action'] : 'player';

// --- CORE EXTRACTION FUNCTION ---
function get_final_stream_url($channel_id) {
    global $channels;
    if (!isset($channels[$channel_id])) return null;

    $selected = $channels[$channel_id];
    $target_url = "https://profamouslife.com/premium.php?player=desktop&live=" . $channel_id;
    $referer = $selected['referer'];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $target_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_REFERER => $referer,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
        CURLOPT_TIMEOUT => 12
    ]);
    
    $html = curl_exec($ch);
    curl_close($ch);

    if (!$html) return null;

    // 1. Reconstruct URL from the JS Array logic
    if (preg_match('/return\s*\(\s*\[\s*"h"\s*,\s*"t"\s*(.*?)\.join\(""\)/s', $html, $matches)) {
        $url_body = str_replace(['"', ',', ' ', "\n", "\r", "\t", "[", "]"], '', $matches[1]);
        $base_url = "ht" . $url_body;

        // 2. Extract the Dynamic Token from the DOM
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        
        // Check multiple possible IDs for the token
        $token_el = $dom->getElementById('enscSiutarfBghaikt') ?: $dom->getElementById('suaeiikScntaBrfthg');
        $token = ($token_el) ? trim($token_el->nodeValue) : "";

        // 3. Final Cleanup
        $final_link = str_replace(['\\', ']', '"', "'"], '', $base_url . $token);
        return trim($final_link);
    }
    return null;
}

// --- ROUTING ---

// If action is get_link, we redirect the player to the final .m3u8
if ($action === 'get_link') {
    $link = get_final_stream_url($id);
    if ($link && strpos($link, 'http') === 0) {
        header("Location: $link");
    } else {
        http_response_code(404);
        echo "Error: Stream link expired or not found.";
    }
    exit;
}

// Default Action: Load the HTML5 Player UI
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Stream - <?php echo $channels[$id]['name']; ?></title>
    
    <link href="https://vjs.zencdn.net/8.10.0/video-js.css" rel="stylesheet" />
    
    <style>
        body { background: #000; color: #fff; font-family: sans-serif; margin: 0; padding: 20px; text-align: center; }
        .player-box { max-width: 960px; margin: 20px auto; background: #111; border: 1px solid #333; border-radius: 8px; overflow: hidden; }
        .nav-bar { margin-top: 20px; display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; }
        .btn { padding: 10px 18px; background: #e91e63; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; transition: 0.2s; }
        .btn:hover { background: #ad1457; }
        .active { background: #666; pointer-events: none; }
    </style>
</head>
<body>

    <h2>Watching: <?php echo $channels[$id]['name']; ?></h2>

    <div class="player-box">
        <video id="my-video" class="video-js vjs-big-play-centered vjs-16-9" controls preload="auto" width="960" data-setup='{}'>
            <source src="index.php?action=get_link&id=<?php echo $id; ?>" type="application/x-mpegURL">
        </video>
    </div>

    <div class="nav-bar">
        <?php foreach ($channels as $key => $val): ?>
            <a href="?id=<?php echo $key; ?>" class="btn <?php echo ($id == $key) ? 'active' : ''; ?>">
                <?php echo $val['name']; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <script src="https://vjs.zencdn.net/8.10.0/video.min.js"></script>
</body>
</html>
