#!/usr/bin/env php
<?php
// Lightweight CLI for refund management

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Refund;
use App\Services\RefundWorkflowService;

$argv0 = $argv;
array_shift($argv0); // script

function usage()
{
    echo "Usage:\n";
    echo "  php bin/refund-cli.php list [--status=STATUS]\n";
    echo "  php bin/refund-cli.php show {id}\n";
    echo "  php bin/refund-cli.php approve {id} [--note='...'] [--by=USER_ID]\n";
    echo "  php bin/refund-cli.php reject {id} --reason='...' [--by=USER_ID]\n";
    echo "  php bin/refund-cli.php cancel {id} --reason='...' [--by=USER_ID]\n";
    echo "  php bin/refund-cli.php return {id} --note='...' [--by=USER_ID]\n";
    echo "  php bin/refund-cli.php complete {id} --payment=REF [--by=USER_ID]\n";
    exit(1);
}

if (count($argv0) === 0) {
    usage();
}

$cmd = array_shift($argv0);

// parse simple --key=value options
$opts = [];
foreach ($argv0 as $a) {
    if (str_starts_with($a, '--')) {
        $p = substr($a, 2);
        $parts = explode('=', $p, 2);
        $opts[$parts[0]] = $parts[1] ?? true;
    } else {
        $opts[] = $a;
    }
}

$container = $app;
$workflow = $container->make(RefundWorkflowService::class);

try {
    switch ($cmd) {
        case 'list':
            $status = $opts['status'] ?? null;
            $query = Refund::query()->with('tickets');
            if ($status) $query->where('current_status', $status);
            $items = $query->orderBy('id', 'desc')->limit(50)->get();
            foreach ($items as $r) {
                echo sprintf("%4d  %-20s  %-15s  %s\n", $r->id, $r->reference ?? '-', $r->current_status ?? '-', $r->email ?? '-');
            }
            break;

        case 'show':
            $id = $opts[0] ?? null;
            if (! $id) usage();
            $r = Refund::with(['tickets','statusLogs','attachments'])->find($id);
            if (! $r) { echo "Refund not found\n"; exit(1); }
            echo json_encode($r->toArray(), JSON_PRETTY_PRINT), "\n";
            break;

        case 'approve':
            $id = $opts[0] ?? null; if (! $id) usage();
            $note = $opts['note'] ?? null; $by = $opts['by'] ?? null;
            $r = Refund::find($id); if (! $r) { echo "Not found\n"; exit(1); }
            $res = $workflow->approve($r, $note, $by);
            echo "Approved: ", $res->id, "\n";
            break;

        case 'reject':
            $id = $opts[0] ?? null; if (! $id) usage();
            $reason = $opts['reason'] ?? null; $by = $opts['by'] ?? null;
            if (! $reason) { echo "--reason required\n"; exit(1); }
            $r = Refund::find($id); if (! $r) { echo "Not found\n"; exit(1); }
            $res = $workflow->reject($r, $reason, $by);
            echo "Rejected: ", $res->id, "\n";
            break;

        case 'cancel':
            $id = $opts[0] ?? null; if (! $id) usage();
            $reason = $opts['reason'] ?? null; $by = $opts['by'] ?? null;
            if (! $reason) { echo "--reason required\n"; exit(1); }
            $r = Refund::find($id); if (! $r) { echo "Not found\n"; exit(1); }
            $res = $workflow->cancel($r, $reason, $by);
            echo "Cancelled: ", $res->id, "\n";
            break;

        case 'return':
            $id = $opts[0] ?? null; if (! $id) usage();
            $note = $opts['note'] ?? null; $by = $opts['by'] ?? null;
            if (! $note) { echo "--note required\n"; exit(1); }
            $r = Refund::find($id); if (! $r) { echo "Not found\n"; exit(1); }
            $res = $workflow->returnBack($r, $note, $by);
            echo "Returned: ", $res->id, "\n";
            break;

        case 'complete':
            $id = $opts[0] ?? null; if (! $id) usage();
            $payment = $opts['payment'] ?? null; $by = $opts['by'] ?? null;
            if (! $payment) { echo "--payment required\n"; exit(1); }
            $r = Refund::find($id); if (! $r) { echo "Not found\n"; exit(1); }
            $res = $workflow->complete($r, $payment, null, null, $by);
            echo "Completed: ", $res->id, "\n";
            break;

        default:
            usage();
    }
} catch (Throwable $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}

exit(0);
