<?php
require_once __DIR__ . '/../../../config/database.php';

class TechLandScraper {
    private $url;
    private $html;
    private $xpath;

    public function __construct($url) {
        $this->url = $url;
        $this->loadPage();
    }

    private function loadPage() {
        $this->html = file_get_contents($this->url);
        $doc = new DOMDocument();
        @$doc->loadHTML($this->html);
        $this->xpath = new DOMXPath($doc);
    }

    public function getImage() {
        $imageSelectors = [
            '//div[contains(@class, "product-image")]//div[contains(@class, "swiper-slide")]//img/@src',
            '//div[contains(@class, "product-image")]//div[contains(@class, "swiper-slide")]//img/@data-largeimg'
        ];

        foreach ($imageSelectors as $selector) {
            $nodes = $this->xpath->query($selector);
            if ($nodes->length > 0) {
                return $nodes->item(0)->nodeValue;
            }
        }
        
        throw new Exception('Image not found on TechLand page');
    }
}

// Handle the AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $productId = $_POST['productId'] ?? null;
        if (!$productId) {
            throw new Exception('Product ID is required');
        }
        
        // Get product URL
        $stmt = $pdo->prepare("SELECT productUrl FROM vendor_prices WHERE productId = ? LIMIT 1");
        $stmt->execute([$productId]);
        $result = $stmt->fetch();
        
        if (!$result) {
            throw new Exception('Product URL not found');
        }

        $scraper = new TechLandScraper($result['productUrl']);
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