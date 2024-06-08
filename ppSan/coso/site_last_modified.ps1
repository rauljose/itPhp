
Get-ChildItem C:\wamp\www\vitex\ -r | where { $_.fullname -notmatch 'txt|pdfs|uploads|testie|idea|vscode|fontawesome-pro-6.4.0-web|vendor'} | Sort-Object LastWriteTime -Descending | Select-Object -first 26 | select @{n='LastWriteTime';e={'{0:yyyy-MMM-dd HH:mm:ss}' -f $_.LastWriteTime}},hack,fullname | Out-String | out-file "C:\wamp\www\vitex\uploads\last" -encoding utf8

$dime = select-string -Path "C:\wamp\www\vitex\uploads\last" -Pattern '\d{4}-.*$'  -List | Select-Object -first 1

$html = "<details class='default' style='margin:1em'><summary>Last: $dime</summary><div><pre class='code'>"

$html | out-file  "C:\wamp\www\vitex\uploads\fragmentLastModified.html" -encoding utf8
