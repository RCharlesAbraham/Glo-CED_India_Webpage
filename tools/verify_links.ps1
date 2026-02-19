$root = Resolve-Path .
$pagesDir = Join-Path $root 'pages'
if (-Not (Test-Path $pagesDir)) { Write-Output "pages directory not found at $pagesDir"; exit 1 }
$files = Get-ChildItem -Path $pagesDir -Filter *.html -Recurse
$urls = @()
foreach ($f in $files) {
    $content = Get-Content $f.FullName -Raw
    $matches = [regex]::Matches($content,'(?:href|src)\s*=\s*"(.*?)"')
    foreach ($m in $matches) { $u = $m.Groups[1].Value; if ($u -and -not $u.StartsWith('#') -and -not $u.StartsWith('mailto:') -and -not $u.StartsWith('javascript:') -and -not $u.StartsWith('http')) { $urls += $u } }
}
$urls = $urls | Select-Object -Unique
Write-Output "Found $($urls.Count) unique relative links/assets to check."
foreach ($u in $urls) {
    $path = $u -replace '^\./',''
    $full = "http://localhost/STP/$path"
    try {
        $r = Invoke-WebRequest -Uri $full -UseBasicParsing -TimeoutSec 10
        Write-Output "$full -> $($r.StatusCode)"
    } catch {
        Write-Output "$full -> ERROR: $($_.Exception.Message)"
    }
}
