<?php
require_once '../secrets.php';
require_once '../database.php';

// ── Security ────────────────────────────────────────────────────────────────
if (!isset($import_user, $import_secret)) {
    die('Add $import_user and $import_secret to secrets.php first.');
}
if (
    !isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] !== $import_user ||
    $_SERVER['PHP_AUTH_PW']  !== $import_secret
) {
    header('WWW-Authenticate: Basic realm="UN/LOCODE Import"');
    http_response_code(401);
    die('Unauthorized.');
}

// ── AJAX endpoints ───────────────────────────────────────────────────────────
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'truncate') {
    header('Content-Type: application/json');
    $db = setupDb();
    $db->query('TRUNCATE TABLE `CodeList`');
    $db->query('TRUNCATE TABLE `subdivision`');
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'import_codelist') {
    header('Content-Type: application/json');
    $rows = json_decode(file_get_contents('php://input'), true);
    if (!$rows) { echo json_encode(['ok' => false, 'error' => 'No data received']); exit; }
    $db = setupDb();
    $sql = 'INSERT INTO `CodeList`
        (`ch`,`country`,`location`,`name`,`nameWoDiacritics`,`subdivision`,
         `status`,`function`,`date`,`IATA`,`coordinates`,`remarks`)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)';
    $stmt = $db->prepare($sql);
    $db->begin_transaction();
    foreach ($rows as $r) {
        $stmt->bind_param('ssssssssssss',
            $r['ch'], $r['country'], $r['location'], $r['name'],
            $r['nameWoDiacritics'], $r['subdivision'], $r['status'],
            $r['function'], $r['date'], $r['IATA'], $r['coordinates'], $r['remarks']
        );
        $stmt->execute();
    }
    $db->commit();
    echo json_encode(['ok' => true, 'inserted' => count($rows)]);
    exit;
}

if ($action === 'import_subdivision') {
    header('Content-Type: application/json');
    $rows = json_decode(file_get_contents('php://input'), true);
    if (!$rows) { echo json_encode(['ok' => false, 'error' => 'No data received']); exit; }
    $db = setupDb();
    $stmt = $db->prepare('INSERT INTO `subdivision` (`countryCode`,`code`,`name`,`type`) VALUES (?,?,?,?)');
    $db->begin_transaction();
    foreach ($rows as $r) {
        $stmt->bind_param('ssss', $r['countryCode'], $r['code'], $r['name'], $r['type']);
        $stmt->execute();
    }
    $db->commit();
    echo json_encode(['ok' => true, 'inserted' => count($rows)]);
    exit;
}

if ($action === 'download_github') {
    header('Content-Type: application/json');
    $allowed = ['code-list.csv', 'subdivision-codes.csv'];
    $file = $_GET['file'] ?? '';
    if (!in_array($file, $allowed, true)) {
        echo json_encode(['ok' => false, 'error' => 'Unknown file']); exit;
    }
    $url = "https://raw.githubusercontent.com/datasets/un-locode/main/data/{$file}";
    $ctx = stream_context_create(['http' => ['timeout' => 30]]);
    $content = @file_get_contents($url, false, $ctx);
    if ($content === false) {
        echo json_encode(['ok' => false, 'error' => "Could not fetch {$file} from GitHub. Check server outbound access."]); exit;
    }
    echo json_encode(['ok' => true, 'content' => $content]);
    exit;
}

if ($action === 'update_sitemap') {
    header('Content-Type: application/json');
    $today  = date('Y-m-d');
    $smPath = __DIR__ . '/../home/sitemapinclude.php';
    $smSrc  = file_get_contents($smPath);
    $smSrc  = preg_replace('/(\$unlocodeLastMod\s*=\s*\')([^\']+)(\')/m', '${1}' . $today . '${3}', $smSrc);
    $smSrc  = preg_replace('/(\$countryLastMod\s*=\s*\')([^\']+)(\')/m',  '${1}' . $today . '${3}', $smSrc);
    file_put_contents($smPath, $smSrc);
    echo json_encode(['ok' => true, 'lastmod' => $today]);
    exit;
}

if ($action === 'update_version') {
    header('Content-Type: application/json');
    $version = trim($_POST['version'] ?? '');
    if (!$version) { echo json_encode(['ok' => false, 'error' => 'No version string provided']); exit; }
    $path = __DIR__ . '/../include.php';
    $src  = file_get_contents($path);
    $src  = preg_replace(
        '/(\$unlocodeVersion\s*=\s*\')([^\']+)(\')/m',
        '${1}' . addslashes($version) . '${3}',
        $src
    );
    file_put_contents($path, $src);
    echo json_encode(['ok' => true]);
    exit;
}

// ── UI ───────────────────────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Import UN/LOCODE data</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>
  <style>
    body { font-family: sans-serif; max-width: 760px; margin: 40px auto; padding: 0 20px; color: #222; }
    h1 { font-size: 1.4rem; }
    h2 { font-size: 1.1rem; margin-top: 2rem; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
    label { display: block; margin: 8px 0 4px; font-weight: bold; font-size: .9rem; }
    input[type=file], input[type=text] { width: 100%; box-sizing: border-box; padding: 6px; border: 1px solid #bbb; border-radius: 4px; }
    button { padding: 8px 18px; border: none; border-radius: 4px; cursor: pointer; font-size: .95rem; }
    .btn-primary  { background: #2563eb; color: #fff; }
    .btn-secondary{ background: #64748b; color: #fff; }
    .btn-danger   { background: #dc2626; color: #fff; }
    button:disabled { opacity: .5; cursor: not-allowed; }
    .progress-wrap { background: #e5e7eb; border-radius: 4px; height: 18px; margin: 8px 0; overflow: hidden; display: none; }
    .progress-bar  { height: 100%; background: #2563eb; width: 0; transition: width .2s; }
    .log { font-size: .85rem; color: #555; margin: 6px 0; min-height: 1.2em; }
    .error { color: #dc2626; }
    .success { color: #16a34a; }
    .section { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin-bottom: 16px; }
    .divider { text-align: center; color: #999; margin: 12px 0; font-size: .85rem; }
  </style>
</head>
<body>
<h1>UN/LOCODE data import</h1>
<p style="color:#888;font-size:.85rem;">Clears both tables and re-imports from scratch. Runs in batches — no timeouts.<br>
⚠ The database is empty between the truncate and the end of the import (~1 min). Don't run this during peak hours.</p>

<h2>Step 1 — Source</h2>
<div class="section">
  <label>code-list.csv</label>
  <input type="file" id="fileCodeList" accept=".csv">
  <div class="divider">— or —</div>
  <button class="btn-secondary" onclick="downloadFromGithub('code-list.csv', 'fileCodeListData', this)">Download from GitHub</button>
  <div class="log" id="logDownloadCodeList"></div>
</div>

<div class="section">
  <label>subdivision-codes.csv</label>
  <input type="file" id="fileSubdivision" accept=".csv">
  <div class="divider">— or —</div>
  <button class="btn-secondary" onclick="downloadFromGithub('subdivision-codes.csv', 'fileSubdivisionData', this)">Download from GitHub</button>
  <div class="log" id="logDownloadSubdivision"></div>
</div>

<h2>Step 2 — Version string</h2>
<div class="section">
  <label>New version label (shown in page footers)</label>
  <input type="text" id="versionString" placeholder="UN/LOCODE 2025-1" value="">
  <div style="font-size:.8rem;color:#888;margin-top:4px;">Leave blank to skip updating the version label in include.php</div>
</div>

<h2>Step 3 — Import</h2>
<button class="btn-danger" id="btnImport" onclick="runImport()">⚠ Clear DB and import</button>
<div class="progress-wrap" id="progressWrap1"><div class="progress-bar" id="bar1"></div></div>
<div class="log" id="logCodeList"></div>
<div class="progress-wrap" id="progressWrap2"><div class="progress-bar" id="bar2"></div></div>
<div class="log" id="logSubdivision"></div>
<div class="log" id="logFinal"></div>

<!-- Hidden stores for GitHub-downloaded content -->
<script>
const BATCH  = 500;

// Storage for GitHub-fetched CSV text (keyed by variable name)
const downloaded = {};

function endpoint(action) {
    return `?action=${action}`;
}

async function downloadFromGithub(filename, storeKey, btn) {
    const logId = filename === 'code-list.csv' ? 'logDownloadCodeList' : 'logDownloadSubdivision';
    const log = document.getElementById(logId);
    btn.disabled = true;
    log.textContent = 'Downloading…';
    log.className = 'log';
    try {
        const res  = await fetch(endpoint('download_github') + '&file=' + encodeURIComponent(filename));
        const json = await res.json();
        if (!json.ok) throw new Error(json.error);
        downloaded[storeKey] = json.content;
        log.textContent = `✓ Downloaded (${Math.round(json.content.length / 1024)} KB)`;
        log.className = 'log success';
    } catch(e) {
        log.textContent = '✗ ' + e.message;
        log.className = 'log error';
        btn.disabled = false;
    }
}

function parseCodeListCSV(text) {
    // Required headers: Change,Country,Location,Name,NameWoDiacritics,Subdivision,Status,Function,Date,IATA,Coordinates,Remarks
    const result = Papa.parse(text, { header: true, skipEmptyLines: true });
    const fields = result.meta.fields ?? [];
    for (const col of ['Change','Country','Location','Name','NameWoDiacritics','Subdivision','Status','Function','Date','IATA','Coordinates','Remarks']) {
        if (!fields.includes(col)) throw new Error(`code-list.csv is missing column "${col}".`);
    }
    return result.data.map(r => ({
        ch:               r['Change'],
        country:          r['Country'],
        location:         r['Location'],
        name:             r['Name'],
        nameWoDiacritics: r['NameWoDiacritics'],
        subdivision:      r['Subdivision'],
        status:           r['Status'],
        function:         r['Function'],
        date:             r['Date'],
        IATA:             r['IATA'],
        coordinates:      r['Coordinates'],
        remarks:          r['Remarks'],
    }));
}

function parseSubdivisionCSV(text) {
    // Required headers: SUCountry,SUCode,SUName,SUType
    // Note: 2025-1 dropped SUType — use the 2024-2 subdivision-codes.csv instead.
    const result = Papa.parse(text, { header: true, skipEmptyLines: true });
    const fields = result.meta.fields ?? [];
    for (const col of ['SUCountry', 'SUCode', 'SUName', 'SUType']) {
        if (!fields.includes(col)) throw new Error(`subdivision-codes.csv is missing column "${col}". Use the 2024-2 version of this file.`);
    }
    return result.data.map(r => ({
        countryCode: r['SUCountry'],
        code:        r['SUCode'],
        name:        r['SUName'],
        type:        r['SUType'],
    }));
}

async function readFileAsText(input) {
    return new Promise((resolve, reject) => {
        if (!input.files || !input.files[0]) return resolve(null);
        const reader = new FileReader();
        reader.onload  = e => resolve(e.target.result);
        reader.onerror = () => reject(new Error('File read error'));
        reader.readAsText(input.files[0]);
    });
}

async function postBatch(action, rows) {
    const res  = await fetch(endpoint(action), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(rows),
    });
    const json = await res.json();
    if (!json.ok) throw new Error(json.error ?? 'Unknown server error');
    return json;
}

async function importInBatches(rows, action, progressWrapId, barId, logId) {
    const wrap = document.getElementById(progressWrapId);
    const bar  = document.getElementById(barId);
    const log  = document.getElementById(logId);
    wrap.style.display = 'block';
    let done = 0;
    const total = rows.length;
    log.className = 'log';
    for (let i = 0; i < total; i += BATCH) {
        const batch = rows.slice(i, i + BATCH);
        await postBatch(action, batch);
        done += batch.length;
        const pct = Math.round(done / total * 100);
        bar.style.width = pct + '%';
        log.textContent = `${action === 'import_codelist' ? 'CodeList' : 'Subdivision'}: ${done.toLocaleString()} / ${total.toLocaleString()} rows (${pct}%)`;
    }
    log.textContent += ' ✓';
    log.className = 'log success';
}

async function runImport() {
    const btn   = document.getElementById('btnImport');
    const final = document.getElementById('logFinal');
    final.textContent = '';
    final.className   = 'log';
    btn.disabled = true;

    try {
        // Gather CSV text from file input or downloaded store
        let codeListText = await readFileAsText(document.getElementById('fileCodeList'));
        if (!codeListText) codeListText = downloaded['fileCodeListData'] ?? null;

        let subdivText = await readFileAsText(document.getElementById('fileSubdivision'));
        if (!subdivText) subdivText = downloaded['fileSubdivisionData'] ?? null;

        if (!codeListText) throw new Error('No code-list.csv provided. Upload a file or download from GitHub first.');
        if (!subdivText)   throw new Error('No subdivision-codes.csv provided. Upload a file or download from GitHub first.');

        // Parse
        document.getElementById('logCodeList').textContent    = 'Parsing code-list.csv…';
        document.getElementById('logSubdivision').textContent = 'Parsing subdivision-codes.csv…';
        const codeRows  = parseCodeListCSV(codeListText);
        const subdivRows = parseSubdivisionCSV(subdivText);

        if (!codeRows.length)  throw new Error('code-list.csv parsed to 0 rows — check format.');
        if (!subdivRows.length) throw new Error('subdivision-codes.csv parsed to 0 rows — check format.');

        // Truncate
        final.textContent = 'Clearing existing data…';
        const trunc = await fetch(endpoint('truncate'));
        const truncJson = await trunc.json();
        if (!truncJson.ok) throw new Error('Truncate failed');

        // Import CodeList
        await importInBatches(codeRows, 'import_codelist', 'progressWrap1', 'bar1', 'logCodeList');

        // Import subdivision
        await importInBatches(subdivRows, 'import_subdivision', 'progressWrap2', 'bar2', 'logSubdivision');

        // Always update sitemap lastmod
        const smRes  = await fetch(endpoint('update_sitemap'));
        const smJson = await smRes.json();
        if (!smJson.ok) throw new Error('Sitemap update failed');

        // Optionally update version label in include.php
        const version = document.getElementById('versionString').value.trim();
        if (version) {
            const vres = await fetch(endpoint('update_version'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'version=' + encodeURIComponent(version),
            });
            const vjson = await vres.json();
            if (!vjson.ok) throw new Error('Version update failed: ' + (vjson.error ?? ''));
        }

        final.textContent = `✓ Done. ${codeRows.length.toLocaleString()} locations and ${subdivRows.length.toLocaleString()} subdivisions imported.` +
                            ` Sitemap lastmod set to ${smJson.lastmod}.` +
                            (version ? ` Version set to "${version}".` : '');
        final.className = 'log success';
    } catch(e) {
        final.textContent = '✗ ' + e.message;
        final.className = 'log error';
    }

    btn.disabled = false;
}
</script>
</body>
</html>
