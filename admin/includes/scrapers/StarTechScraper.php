<?php
require_once __DIR__ . '/../../../config/database.php';

class StarTechScraper {
    private $url;
    private $html;
    private $xpath;

    public function __construct($url) {
        $this->url = $url;
        $this->loadPage();
    }

    private function loadPage() {
        // Add user agent to avoid blocking
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ]
        ];
        $context = stream_context_create($opts);
        $this->html = file_get_contents($this->url, false, $context);
        
        if ($this->html === false) {
            throw new Exception('Failed to load the product page');
        }

        $doc = new DOMDocument();
        @$doc->loadHTML($this->html);
        $this->xpath = new DOMXPath($doc);
    }

    public function getImage() {
        $imageSelectors = [
            '//div[@class="product-img-holder"]//img[@class="main-img"]/@src',
            '//meta[@itemprop="image"]/@content'
        ];

        foreach ($imageSelectors as $selector) {
            $nodes = $this->xpath->query($selector);
            if ($nodes->length > 0) {
                return $nodes->item(0)->nodeValue;
            }
        }
        
        throw new Exception('Image not found on StarTech page');
    }
}

// Handle the AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $productId = $_POST['productId'] ?? null;
        if (!$productId) {
            throw new Exception('Product ID is required');
        }
        
        // Get StarTech product URL
        $stmt = $pdo->prepare("
            SELECT vp.productUrl 
            FROM vendor_prices vp 
            JOIN vendors v ON v.vendorId = vp.vendorId 
            WHERE vp.productId = ? 
            AND v.vendorName LIKE '%startech%' 
            LIMIT 1
        ");
        $stmt->execute([$productId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            throw new Exception('StarTech product URL not found');
        }

        $scraper = new StarTechScraper($result['productUrl']);
        $imageUrl = $scraper->getImage();
        
        // Update database with new image URL
        $stmt = $pdo->prepare("UPDATE products SET productImage = ? WHERE productId = ?");
        $stmt->execute([$imageUrl, $productId]);
        
        echo json_encode([
            'error' => false,
            'message' => 'Image URL updated successfully',
            'imagePath' => $imageUrl
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => true,
            'message' => $e->getMessage()
        ]);
    }
} 