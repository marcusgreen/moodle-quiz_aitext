<?php
// Simple test script to verify aitext functionality
// This is for development testing only

require_once(__DIR__ . '/classes/aitext.php');
require_once(__DIR__ . '/classes/aibridge.php');

echo "Testing aitext class functionality...\n\n";

// Test 1: Create aitext instance
try {
    $contextid = 123;
    $aitext = new quiz_aitext\aitext($contextid);
    echo "✓ aitext instance created successfully\n";
    echo "✓ Context ID: " . $aitext->get_contextid() . "\n";
    
    // Test AI bridge access
    $aibridge = $aitext->get_aibridge();
    echo "✓ AI bridge instance accessible\n";
    
} catch (Exception $e) {
    echo "✗ Error creating aitext instance: " . $e->getMessage() . "\n";
}

// Test 2: Test statistics calculation
echo "\nTesting statistics calculation...\n";
try {
    $attempts = [
        (object) ['grade' => 80, 'state' => 'finished', 'timespent' => 1200],
        (object) ['grade' => 90, 'state' => 'finished', 'timespent' => 1500],
        (object) ['grade' => 70, 'state' => 'finished', 'timespent' => 1800],
        (object) ['grade' => 0, 'state' => 'abandoned', 'timespent' => 300]
    ];
    
    $statistics = $aitext->calculate_statistics($attempts, 20, 100);
    
    echo "✓ Total attempts: " . $statistics['total_attempts'] . "\n";
    echo "✓ Average score: " . $statistics['average_score'] . "%\n";
    echo "✓ Highest score: " . $statistics['highest_score'] . "%\n";
    echo "✓ Lowest score: " . $statistics['lowest_score'] . "%\n";
    echo "✓ Completion rate: " . $statistics['completion_rate'] . "%\n";
    echo "✓ Average time: " . $statistics['average_time'] . " minutes\n";
    
} catch (Exception $e) {
    echo "✗ Error calculating statistics: " . $e->getMessage() . "\n";
}

// Test 3: Test AI analysis generation (should return 'AI Feedback' in test environment)
echo "\nTesting AI analysis generation...\n";
try {
    $quiz = (object) ['name' => 'Test Quiz', 'grade' => 100, 'intro' => 'Test quiz'];
    $cm = (object) ['id' => 456];
    $course = (object) ['fullname' => 'Test Course'];
    
    $attempts = [
        (object) ['firstname' => 'John', 'lastname' => 'Doe', 'grade' => 85, 'state' => 'finished', 'timespent' => 1200]
    ];
    
    $statistics = [
        'total_attempts' => 1,
        'average_score' => 85.0,
        'highest_score' => 85.0,
        'lowest_score' => 85.0,
        'completion_rate' => 100.0,
        'average_time' => 20.0,
        'total_questions' => 10
    ];
    
    $analysis = $aitext->generate_quiz_analysis($quiz, $cm, $course, $attempts, $statistics);
    echo "✓ AI analysis generated: " . substr($analysis, 0, 50) . "...\n";
    
} catch (Exception $e) {
    echo "✗ Error generating AI analysis: " . $e->getMessage() . "\n";
}

echo "\nTest completed!\n";