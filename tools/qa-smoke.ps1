param([uri]$BaseUri = 'https://localhost/gms/')

# Anonymous GET checks only; no cookies, credentials or application writes.
$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Net.Http
$handler = [System.Net.Http.HttpClientHandler]::new()
$handler.AllowAutoRedirect = $false
$handler.UseCookies = $false
$client = [System.Net.Http.HttpClient]::new($handler)
$client.Timeout = [TimeSpan]::FromSeconds(15)
$failed = 0

try {
    foreach ($path in @(
        'login.php',
        'index.php',
        'daily_diary.php',
        'contract_list.php',
        'contractor_list.php',
        'new_contract.php',
        'new_contractor.php',
        'zone.php',
        'add_zone.php',
        'update_zone.php',
        'add_subzone.php',
        'update_sz.php',
        'users_management.php',
        'sitedaily_records.php',
        'maps.php',
        'assets/css/modern-dashboard.css',
        'assets/images/gms.png'
    )) {
        $timer = [Diagnostics.Stopwatch]::StartNew()
        $response = $client.GetAsync([uri]::new($BaseUri, $path)).GetAwaiter().GetResult()
        try {
            $status = [int]$response.StatusCode
            $public = $path -in @('login.php', 'assets/css/modern-dashboard.css', 'assets/images/gms.png')
            $expected = if ($public) { 200 } else { 302 }
            $passed = $status -eq $expected
            if (!$public) {
                $location = $response.Headers.Location
                $passed = $passed -and $null -ne $location -and ([uri]::new($BaseUri, $location)).AbsoluteUri -eq ([uri]::new($BaseUri, 'login.php')).AbsoluteUri
            }
            if (!$passed) { $failed++ }
            $result = if ($passed) { 'PASS' } else { 'FAIL' }
            Write-Output ('{0} {1} status={2} expected={3} elapsed_ms={4}' -f $result, $path, $status, $expected, $timer.ElapsedMilliseconds)
        } finally {
            $response.Dispose()
        }
    }
} finally {
    $client.Dispose()
    $handler.Dispose()
}

if ($failed) { throw "$failed smoke check(s) failed." }
