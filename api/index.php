<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SPORTSBD Pro - Live Stream</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/darkly/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a; /* Deep Navy */
            color: #f8fafc;
        }

        .navbar {
            background: rgba(15, 23, 42, 0.8) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Video Player Styling */
        .player-container {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), 0 0 15px rgba(59, 130, 246, 0.2);
            background: #000;
        }

        .player-wrapper {
            position: relative;
            padding-top: 56.25%; /* 16:9 */
        }

        .player-wrapper video {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
        }

        /* Glassmorphism Cards */
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
        }

        .card-header {
            background: rgba(255, 255, 255, 0.03);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.85rem;
        }

        /* Modern Channel Grid */
        #channel-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 12px;
            max-height: 500px;
            overflow-y: auto;
            padding-right: 5px;
        }

        /* Custom Scrollbar */
        #channel-grid::-webkit-scrollbar { width: 5px; }
        #channel-grid::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }

        .channel-btn {
            background: #1e293b;
            border: 1px solid #334155;
            color: #94a3b8;
            padding: 15px 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .channel-btn:hover {
            background: #334155;
            color: #fff;
            transform: translateY(-3px);
            border-color: #3b82f6;
        }

        .channel-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #60a5fa;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.4);
        }

        .channel-btn i {
            font-size: 1.2rem;
        }

        #streamLinkCard {
            font-family: 'JetBrains Mono', monospace;
            background: #000;
            color: #10b981; /* Emerald Green */
            padding: 10px;
            border-radius: 6px;
            font-size: 0.8rem;
        }

        .status-dot {
            height: 8px; width: 8px;
            background-color: #10b981;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
            box-shadow: 0 0 8px #10b981;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-play-circle text-primary me-2"></i>SPORTS<span class="text-primary">BD</span>
            </a>
            <div class="ms-auto">
                 <a class="btn btn-primary btn-sm rounded-pill px-3" href="playlist">
                    <i class="fas fa-cloud-download-alt me-1"></i> M3U Playlist
                </a>
            </div>
        </div>
    </nav>

    <main class="container mt-4">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="player-container mb-4">
                    <div class="player-wrapper">
                        <video id="videoPlayer" controls autoplay muted></video>
                    </div>
                </div>
                
                <div class="card glass-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-link me-2"></i>Source URL</span>
                        <span class="badge bg-success"><div class="status-dot"></div> Live</span>
                    </div>
                    <div class="card-body">
                        <div id="streamLinkCard">
                            <code id="streamLink" class="text-success">Initializing stream connection...</code>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card glass-card h-100">
                    <div class="card-header">
                        <i class="fas fa-th-large me-2"></i>Available Channels
                    </div>
                    <div class="card-body">
                        <div id="channel-grid">
                            <div class="text-center w-100 py-5">
                                <div class="spinner-border text-primary" role="status"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="mt-5 py-4 border-top border-secondary">
        <div class="container text-center">
            <p class="text-muted small">© 2026 SPORTSBD | Developed by BDDEVELOPER</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('videoPlayer');
            const streamLinkEl = document.getElementById('streamLink');
            const channelGrid = document.getElementById('channel-grid');

            let hls = null;
            let currentChannel = 'skyscric';
            let channelsData = {};

            function loadStream(channelId) {
                if (hls) hls.destroy();
                if (!Hls.isSupported()) return alert("HLS not supported.");

                hls = new Hls({ lowLatencyMode: true });
                hls.loadSource(`player/?channel=${channelId}`);
                hls.attachMedia(video);
                hls.on(Hls.Events.MANIFEST_PARSED, () => video.play());
            }

            function updateStreamLink(channelId) {
                streamLinkEl.textContent = 'Requesting secure link...';
                fetch(`player/?action=get_link&channel=${channelId}`)
                    .then(r => r.text())
                    .then(link => { streamLinkEl.textContent = link; })
                    .catch(() => { streamLinkEl.textContent = 'Error connecting to source.'; });
            }

            function renderChannelGrid() {
                channelGrid.innerHTML = '';
                for (const id in channelsData) {
                    const channel = channelsData[id];
                    const btn = document.createElement('div');
                    btn.className = 'channel-btn';
                    btn.dataset.channel = id;
                    btn.innerHTML = `
                        <i class="fas fa-tv"></i>
                        <span>${channel.name}</span>
                    `;
                    btn.onclick = () => selectChannel(id);
                    channelGrid.appendChild(btn);
                }
                updateActiveUI(currentChannel);
            }

            function selectChannel(id) {
                if (id === currentChannel) return;
                currentChannel = id;
                loadStream(id);
                updateStreamLink(id);
                updateActiveUI(id);
            }

            function updateActiveUI(id) {
                document.querySelectorAll('.channel-btn').forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.channel === id);
                });
            }

            // Initial Fetch
            fetch('player/?action=get_channels')
                .then(r => r.json())
                .then(data => {
                    channelsData = data;
                    renderChannelGrid();
                    loadStream(currentChannel);
                    updateStreamLink(currentChannel);
                })
                .catch(err => {
                    channelGrid.innerHTML = '<p class="text-danger">Failed to load channels.</p>';
                });
        });
    </script>
</body>
</html>
