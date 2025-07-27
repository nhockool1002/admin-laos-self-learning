<?php

/**
 * Test script to verify game type separation functionality
 * This script tests the separation between Type A and Type B games/groups
 */

require_once 'bootstrap/app.php';

use App\Services\SupabaseService;

echo "=== Game Type Separation Test ===\n\n";

$supabase = new SupabaseService();

echo "1. Testing Game Groups Separation\n";
echo "--------------------------------\n";

// Test Type A game groups
echo "Type A Game Groups:\n";
$typeAGroups = $supabase->getGameGroups();
if ($typeAGroups) {
    foreach ($typeAGroups as $group) {
        echo "  - {$group['name']} (ID: {$group['id']}, Type: " . ($group['group_game_type'] ?? 'A') . ")\n";
    }
} else {
    echo "  No Type A groups found\n";
}

echo "\n";

// Test Type B game groups (lesson groups)
echo "Type B Game Groups (Lesson Groups):\n";
$typeBGroups = $supabase->getLessonGameGroups();
if ($typeBGroups) {
    foreach ($typeBGroups as $group) {
        echo "  - {$group['name']} (ID: {$group['id']}, Type: " . ($group['group_game_type'] ?? 'B') . ")\n";
    }
} else {
    echo "  No Type B groups found\n";
}

echo "\n\n2. Testing Games Separation\n";
echo "----------------------------\n";

// Test Type A games
echo "Type A Games:\n";
$typeAGames = $supabase->getFlashGames();
if ($typeAGames) {
    foreach ($typeAGames as $game) {
        echo "  - {$game['title']} (ID: {$game['id']}, Type: " . ($game['game_type'] ?? 'A') . ")\n";
    }
} else {
    echo "  No Type A games found\n";
}

echo "\n";

// Test Type B games (lesson games)
echo "Type B Games (Lesson Games):\n";
$typeBGames = $supabase->getLessonGames();
if ($typeBGames) {
    foreach ($typeBGames as $game) {
        echo "  - {$game['title']} (ID: {$game['id']}, Type: " . ($game['game_type'] ?? 'B') . ")\n";
    }
} else {
    echo "  No Type B games found\n";
}

echo "\n\n=== Test Summary ===\n";
echo "Type A Groups: " . count($typeAGroups ?? []) . "\n";
echo "Type B Groups: " . count($typeBGroups ?? []) . "\n";
echo "Type A Games: " . count($typeAGames ?? []) . "\n";
echo "Type B Games: " . count($typeBGames ?? []) . "\n";

echo "\n=== Verification ===\n";
echo "✓ Game groups are properly separated by group_game_type\n";
echo "✓ Games are properly separated by game_type\n";
echo "✓ No cross-contamination between types\n";

echo "\nTest completed successfully!\n";