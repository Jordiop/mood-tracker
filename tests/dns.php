<?php
// Save as test.php and run: php test.php
$host = 'aws-1-eu-west-2.pooler.supabase.com';
echo "Testing DNS for: $host\n";
$ip = gethostbyname($host);
echo "Result: $ip\n";
echo ($ip === $host) ? "❌ DNS FAILED\n" : "✅ DNS OK\n";

// Also test with dig/nslookup equivalent
$dnsRecords = dns_get_record($host, DNS_A);
print_r($dnsRecords);