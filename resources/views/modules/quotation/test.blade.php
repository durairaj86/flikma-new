<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Material Design Style Search</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fafafa;
            font-family: Roboto, Arial, sans-serif;
        }
        .search-container {
            max-width: 600px;
            margin: 60px auto;
            position: relative;
        }
        .md-search-box {
            display: flex;
            align-items: center;
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 2px 5px rgba(0,0,0,.2);
            padding: 0 12px;
            transition: box-shadow .2s;
        }
        .md-search-box:focus-within {
            box-shadow: 0 4px 8px rgba(0,0,0,.3);
        }
        .md-search-box input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 16px;
            padding: 10px;
        }
        .md-search-box button {
            border: none;
            background: transparent;
            font-size: 20px;
            color: #5f6368;
            cursor: pointer;
            padding: 8px;
        }
        .search-results {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,.2);
            max-height: 300px;
            overflow-y: auto;
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            margin-top: 6px;
            z-index: 1000;
        }
        .search-results .result-item {
            padding: 12px 16px;
            cursor: pointer;
            transition: background .2s;
        }
        .search-results .result-item:hover,
        .search-results .result-item.active {
            background: #f1f3f4;
        }
    </style>
</head>
<body>
<div class="search-container">
    <div class="md-search-box">
        <button id="searchBtn">🔍</button>
        <input type="text" id="searchInput" placeholder="Search Google or type a URL">
    </div>
    <div class="search-results" id="searchResults"></div>
</div>

<script>
    (function(){
        const searchInput = document.getElementById('searchInput');
        const resultsBox = document.getElementById('searchResults');
        const searchBtn = document.getElementById('searchBtn');

        const suggestions = [
            'Gmail login',
            'Google Drive',
            'Material Design guidelines',
            'YouTube trending',
            'Weather today',
            'News headlines',
            'Flipkart offers',
            'Amazon best sellers',
            'Nearby restaurants',
            'Latest movies'
        ];

        let currentIndex = -1;

        function renderResults(filtered) {
            resultsBox.innerHTML = '';
            if (filtered.length === 0) {
                resultsBox.style.display = 'none';
                return;
            }
            filtered.forEach(item => {
                const div = document.createElement('div');
                div.className = 'result-item';
                div.textContent = item;
                div.addEventListener('click', () => selectItem(item));
                resultsBox.appendChild(div);
            });
            resultsBox.style.display = 'block';
        }

        function selectItem(text) {
            searchInput.value = text;
            resultsBox.style.display = 'none';
            console.log('Selected:', text);
        }

        function filter(query) {
            const q = query.toLowerCase();
            return suggestions.filter(p => p.toLowerCase().includes(q));
        }

        searchInput.addEventListener('input', () => {
            const filtered = filter(searchInput.value);
            renderResults(filtered);
            currentIndex = -1;
        });

        searchInput.addEventListener('keydown', (e) => {
            const items = resultsBox.querySelectorAll('.result-item');
            if (items.length === 0) return;

            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    currentIndex = (currentIndex + 1) % items.length;
                    updateActive(items);
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    currentIndex = (currentIndex - 1 + items.length) % items.length;
                    updateActive(items);
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (currentIndex >= 0) selectItem(items[currentIndex].textContent);
                    break;
                case 'Escape':
                    resultsBox.style.display = 'none';
                    break;
            }
        });

        function updateActive(items) {
            items.forEach(el => el.classList.remove('active'));
            if (currentIndex >= 0) {
                items[currentIndex].classList.add('active');
                items[currentIndex].scrollIntoView({ block: 'nearest' });
            }
        }

        searchBtn.addEventListener('click', () => {
            console.log('Search submitted:', searchInput.value);
            resultsBox.style.display = 'none';
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-container')) {
                resultsBox.style.display = 'none';
            }
        });
    })();
</script>
</body>
</html>
