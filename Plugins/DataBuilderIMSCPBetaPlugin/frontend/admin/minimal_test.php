<?php
// Absolute minimal test
file_put_contents('/tmp/databuilder_route_test.txt', 'ROUTE WORKS ' . date('Y-m-d H:i:s'));
echo 'Route is working! Check /tmp/databuilder_route_test.txt';
