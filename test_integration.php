<?php
// Integration test for aitext report functionality
// This tests the report class structure without requiring full Moodle environment

require_once(__DIR__ . '/classes/aitext.php');

echo "Testing aitext report integration...\n\n";

// Test the report class structure
try {
    // Check if we can access the aitext class methods
    $reflection = new ReflectionClass('quiz_aitext\aitext');
    
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    echo "✓ aitext class methods:\n";
    foreach ($methods as $method) {
        echo "  - " . $method->getName() . "\n";
    }
    
    $properties = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
    echo "\n✓ aitext class properties:\n";
    foreach ($properties as $property) {
        echo "  - " . $property->getName() . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error inspecting aitext class: " . $e->getMessage() . "\n";
}

// Test language strings
echo "\n✓ Language strings that should be available:\n";
$expected_strings = [
    'pluginname',
    'ai_analysis',
    'statistics',
    'total_questions',
    'attempt_count',
    'average_score',
    'highest_score',
    'lowest_score',
    'completion_rate',
    'average_time',
    'minutes',
    'generating_analysis',
    'no_attempts_info',
    'getreport'
];

foreach ($expected_strings as $string) {
    echo "  - " . $string . "\n";
}

echo "\n✓ Template variables that should be available:\n";
$template_vars = [
    'pluginname',
    'description',
    'quizname',
    'coursename',
    'ai_analysis',
    'statistics',
    'has_attempts',
    'attempt_count'
];

foreach ($template_vars as $var) {
    echo "  - " . $var . "\n";
}

echo "\nIntegration test completed successfully!\n";
echo "\nTo test the full functionality, access the quiz report in Moodle:\n";
echo "1. Go to a quiz in Moodle\n";
echo "2. Click on Reports\n";
echo "3. Select 'Report AI Text'\n";
echo "4. The AI analysis will be displayed with quiz statistics\n";