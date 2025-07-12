<?php

require_once '../config/database.php';

try {
    $config = require '../config.php';
    
    $database = new Database($config);
    
    $sqlFile = __DIR__ . '/schema.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Schema file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $database->getConnection()->exec($statement);
        }
    }
    
    echo "✅ Database tables created successfully!\n";
    echo "📊 Tables created:\n";
    echo "   - Users\n";
    echo "   - Posts\n";
    echo "🔗 Relationships established\n";
    echo "📈 Indexes created for optimization\n";
    echo "🎯 Sample data inserted\n";
    
} catch (Exception $e) {
    echo "❌ Error setting up database: " . $e->getMessage() . "\n";
    exit(1);
}
?> 