<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\StripeService;
use App\Models\UserSubscription;
use Stripe\Subscription;
use Exception;

class SyncStripeSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:sync-subscriptions {--limit=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Stripe subscriptions with local database';

    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        parent::__construct();
        $this->stripeService = $stripeService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Stripe subscription sync...');

        $limit = $this->option('limit');
        $synced = 0;
        $errors = 0;

        try {
            // Get all subscriptions from Stripe
            $subscriptions = Subscription::all(['limit' => $limit]);

            $this->info("Found {$subscriptions->count()} subscriptions to sync");

            foreach ($subscriptions->data as $stripeSubscription) {
                try {
                    $this->stripeService->syncSubscription($stripeSubscription->id);
                    $synced++;
                    $this->line("✓ Synced subscription: {$stripeSubscription->id}");
                } catch (Exception $e) {
                    $errors++;
                    $this->error("✗ Failed to sync subscription {$stripeSubscription->id}: {$e->getMessage()}");
                }
            }

            $this->info("\nSync completed!");
            $this->info("Successfully synced: {$synced}");
            $this->info("Errors: {$errors}");

        } catch (Exception $e) {
            $this->error("Sync failed: {$e->getMessage()}");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}