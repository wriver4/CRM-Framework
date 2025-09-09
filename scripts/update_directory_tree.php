<?php
/**
 * Directory Tree Update Script
 * 
 * This script automatically updates the directory tree documentation in .zencoder/rules/repo.md
 * when structural changes are made to the repository.
 * 
 * Usage:
 *   php scripts/update_directory_tree.php
 *   php scripts/update_directory_tree.php --check-only
 * 
 * The script:
 * 1. Generates current directory tree using .gitignore filters
 * 2. Compares with existing documentation
 * 3. Updates the repo.md file if changes are detected
 * 4. Provides summary of changes made
 */

class DirectoryTreeUpdater {
    private $repoRoot;
    private $repoMdPath;
    private $gitignorePath;
    private $checkOnly = false;
    
    public function __construct() {
        $this->repoRoot = dirname(__DIR__);
        $this->repoMdPath = $this->repoRoot . '/.zencoder/rules/directory-structure.md';
        $this->gitignorePath = $this->repoRoot . '/.gitignore';
    }
    
    public function run($checkOnly = false) {
        $this->checkOnly = $checkOnly;
        
        echo "🌳 Directory Tree Updater\n";
        echo "========================\n\n";
        
        // Check if required files exist
        if (!$this->validateFiles()) {
            return false;
        }
        
        // Generate current tree
        echo "📁 Generating current directory tree...\n";
        $currentTree = $this->generateDirectoryTree();
        
        if (!$currentTree) {
            echo "❌ Failed to generate directory tree\n";
            return false;
        }
        
        // Read existing documentation
        echo "📖 Reading existing documentation...\n";
        $existingContent = file_get_contents($this->repoMdPath);
        
        // Extract current tree from documentation
        $existingTree = $this->extractExistingTree($existingContent);
        
        // Compare trees
        if ($this->treesAreEqual($currentTree, $existingTree)) {
            echo "✅ Directory tree is up to date - no changes needed\n";
            return true;
        }
        
        if ($this->checkOnly) {
            echo "⚠️  Directory tree is outdated but --check-only flag is set\n";
            echo "📝 Run without --check-only to update the documentation\n";
            return false;
        }
        
        // Update documentation
        echo "🔄 Updating directory tree documentation...\n";
        $success = $this->updateDocumentation($existingContent, $currentTree);
        
        if ($success) {
            echo "✅ Directory tree documentation updated successfully\n";
            echo "📝 Changes have been saved to .zencoder/rules/directory-structure.md\n";
        } else {
            echo "❌ Failed to update directory tree documentation\n";
        }
        
        return $success;
    }
    
    private function validateFiles() {
        if (!file_exists($this->repoMdPath)) {
            echo "❌ Repository documentation file not found: {$this->repoMdPath}\n";
            return false;
        }
        
        if (!file_exists($this->gitignorePath)) {
            echo "⚠️  .gitignore file not found, using default filters\n";
        }
        
        return true;
    }
    
    private function generateDirectoryTree() {
        // Read .gitignore patterns and add documentation-specific filters
        $ignorePatterns = $this->getAllIgnorePatterns();
        
        // Build tree command with ignore patterns
        $ignoreFlags = $this->buildIgnoreFlags($ignorePatterns);
        
        // Generate tree (limit to 4 levels for readability)
        $command = "tree '{$this->repoRoot}' {$ignoreFlags} -L 4 2>/dev/null";
        
        $output = shell_exec($command);
        
        if (!$output) {
            echo "⚠️  Tree command failed, trying alternative method...\n";
            return $this->generateTreeAlternative();
        }
        
        return $this->cleanTreeOutput($output);
    }
    
    private function getGitignorePatterns() {
        if (!file_exists($this->gitignorePath)) {
            return $this->getDefaultIgnorePatterns();
        }
        
        $content = file_get_contents($this->gitignorePath);
        $lines = explode("\n", $content);
        $patterns = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            // Skip comments and empty lines
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            
            // Remove trailing slashes for tree command
            $pattern = rtrim($line, '/');
            if (!empty($pattern)) {
                $patterns[] = $pattern;
            }
        }
        
        return $patterns;
    }
    
    private function getDefaultIgnorePatterns() {
        return [
            'vendor', 'node_modules', '.git', 'logs', '.vscode',
            '.conf', '.pki', '.trash', '.zencoder', 'cwp_stats',
            'backupcwp', 'tmp', 'ssl', 'ftp',
            'error.log', 'php_errors.log', 'request*.log',
            'conf.json', 'passwd', 'repo.md'
        ];
    }
    
    private function getAllIgnorePatterns() {
        // Combine .gitignore patterns with additional documentation-specific filters
        $gitignorePatterns = $this->getGitignorePatterns();
        $additionalPatterns = ['vendor', 'node_modules', 'logs'];
        
        // Remove Archive from ignore patterns - we want it in documentation even though it's in .gitignore
        $allPatterns = array_unique(array_merge($gitignorePatterns, $additionalPatterns));
        $allPatterns = array_filter($allPatterns, function($pattern) {
            return $pattern !== 'Archive';
        });
        
        return $allPatterns;
    }
    
    private function buildIgnoreFlags($patterns) {
        if (empty($patterns)) {
            return '';
        }
        
        $ignoreList = implode('|', $patterns);
        return "-I '{$ignoreList}'";
    }
    
    private function generateTreeAlternative() {
        // Fallback method using find and manual tree building
        echo "🔄 Using alternative tree generation method...\n";
        
        $ignorePatterns = $this->getGitignorePatterns();
        $findCommand = "find '{$this->repoRoot}' -type f \\( -name '*.php' -o -name '*.md' -o -name '*.sql' -o -name '*.json' -o -name '*.js' -o -name '*.css' \\) | head -200 | sort";
        
        $files = shell_exec($findCommand);
        if (!$files) {
            return null;
        }
        
        // Build a simple tree representation
        $fileList = explode("\n", trim($files));
        return $this->buildSimpleTree($fileList);
    }
    
    private function buildSimpleTree($files) {
        $tree = "democrm/\n";
        $directories = [];
        
        foreach ($files as $file) {
            if (empty($file)) continue;
            
            $relativePath = str_replace($this->repoRoot . '/', '', $file);
            $parts = explode('/', $relativePath);
            
            // Build directory structure
            $currentPath = '';
            for ($i = 0; $i < count($parts) - 1; $i++) {
                $currentPath .= $parts[$i] . '/';
                if (!in_array($currentPath, $directories)) {
                    $directories[] = $currentPath;
                }
            }
        }
        
        sort($directories);
        
        foreach ($directories as $dir) {
            $level = substr_count($dir, '/') - 1;
            $indent = str_repeat('│   ', $level);
            $dirName = basename(rtrim($dir, '/'));
            $tree .= $indent . "├── {$dirName}/\n";
        }
        
        return $tree;
    }
    
    private function cleanTreeOutput($output) {
        // Remove the first line (root path) and clean up
        $lines = explode("\n", $output);
        if (count($lines) > 0) {
            array_shift($lines); // Remove first line (the "." root path)
        }
        
        // Prepend 'democrm/' as the root, keeping all directory structure intact
        array_unshift($lines, 'democrm/');
        
        return implode("\n", $lines);
    }
    
    private function extractExistingTree($content) {
        // Extract the tree section from the markdown
        $pattern = '/```\n(democrm\/.*?)\n```/s';
        
        if (preg_match($pattern, $content, $matches)) {
            return trim($matches[1]);
        }
        
        return '';
    }
    
    private function treesAreEqual($tree1, $tree2) {
        // Normalize whitespace and compare
        $normalized1 = preg_replace('/\s+/', ' ', trim($tree1));
        $normalized2 = preg_replace('/\s+/', ' ', trim($tree2));
        
        return $normalized1 === $normalized2;
    }
    
    private function updateDocumentation($content, $newTree) {
        // Find the directory structure section and replace it
        $pattern = '/(# Directory Structure.*?```\n).*?(\n```)/s';
        
        $replacement = '$1' . $newTree . '$2';
        
        $updatedContent = preg_replace($pattern, $replacement, $content);
        
        if ($updatedContent === null || $updatedContent === $content) {
            echo "⚠️  Could not find directory structure section to update\n";
            return false;
        }
        
        return file_put_contents($this->repoMdPath, $updatedContent) !== false;
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    $checkOnly = in_array('--check-only', $argv);
    
    $updater = new DirectoryTreeUpdater();
    $success = $updater->run($checkOnly);
    
    exit($success ? 0 : 1);
} else {
    echo "This script must be run from the command line.\n";
    exit(1);
}