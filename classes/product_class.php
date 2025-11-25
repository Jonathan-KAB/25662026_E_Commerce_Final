<?php
require_once __DIR__ . '/../settings/db_class.php';

class Product extends db_connection
{
    public $last_query = '';
    private function esc($val)
    {
        if (!$this->db_connect()) return '';
        return mysqli_real_escape_string($this->db, (string)$val);
    }

    /**
     * Insert a new product. $data expects keys: product_cat, product_brand, product_title, product_price, product_desc, product_keywords
     * Returns inserted product_id on success, false on failure.
     */
    public function addProduct(array $data)
    {
        $cat = isset($data['product_cat']) ? (int)$data['product_cat'] : 0;
        $brand = isset($data['product_brand']) ? (int)$data['product_brand'] : 0;
        $title = isset($data['product_title']) ? $this->esc($data['product_title']) : '';
        $price = isset($data['product_price']) ? (float)$data['product_price'] : 0.0;
        $desc = isset($data['product_desc']) ? $this->esc($data['product_desc']) : '';
        $keywords = isset($data['product_keywords']) ? $this->esc($data['product_keywords']) : '';
        $seller_id = isset($data['seller_id']) ? (int)$data['seller_id'] : 0;
        $product_type = isset($data['product_type']) ? $this->esc($data['product_type']) : 'fabric';
        
        // Services don't track inventory, so they always get stock 999. Other products use whatever was passed in.
        $stock = ($product_type === 'service') ? 999 : (isset($data['product_stock']) ? (int)$data['product_stock'] : 0);

        $sql = "INSERT INTO products (product_cat, product_brand, product_title, product_price, product_desc, product_keywords, product_stock, seller_id, product_type) "
            . "VALUES ($cat, $brand, '$title', $price, '$desc', '$keywords', $stock, $seller_id, '$product_type')";
        $this->last_query = $sql;

        if ($this->db_write_query($sql)) {
            return $this->last_insert_id();
        }
        return false;
    }

    /**
     * Update an existing product. $data may contain same keys as addProduct.
     */
    public function updateProduct($product_id, array $data)
    {
        $product_id = (int)$product_id;
        $sets = [];
        if (isset($data['product_cat'])) $sets[] = 'product_cat = ' . (int)$data['product_cat'];
        if (isset($data['product_brand'])) $sets[] = 'product_brand = ' . (int)$data['product_brand'];
        if (isset($data['product_title'])) $sets[] = "product_title = '" . $this->esc($data['product_title']) . "'";
        if (isset($data['product_price'])) $sets[] = 'product_price = ' . (float)$data['product_price'];
        if (isset($data['product_desc'])) $sets[] = "product_desc = '" . $this->esc($data['product_desc']) . "'";
        if (isset($data['product_keywords'])) $sets[] = "product_keywords = '" . $this->esc($data['product_keywords']) . "'";
        if (isset($data['product_image'])) $sets[] = "product_image = '" . $this->esc($data['product_image']) . "'";
        if (isset($data['product_type'])) $sets[] = "product_type = '" . $this->esc($data['product_type']) . "'";
        
        // Again, services don't need inventory tracking
        if (isset($data['product_stock'])) {
            $product_type = isset($data['product_type']) ? $data['product_type'] : null;
            // If it's a service, force stock to 999
            $stock = ($product_type === 'service') ? 999 : (int)$data['product_stock'];
            $sets[] = 'product_stock = ' . $stock;
        }

        if (empty($sets)) return false;

        $sql = "UPDATE products SET " . implode(', ', $sets) . " WHERE product_id = $product_id";
        return $this->db_write_query($sql);
    }

    /**
     * Fetch all products with category and brand names attached.
     * Returns array on success, false on DB error.
     */
    public function fetchAllProducts()
    {
        $sql = "SELECT p.*, c.cat_name, b.brand_name FROM products p 
                LEFT JOIN categories c ON p.product_cat = c.cat_id 
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                ORDER BY p.product_id DESC";
        return $this->db_fetch_all($sql);
    }

    /**
     * Get a single product by id
     */
    public function getProductById($product_id)
    {
        $product_id = (int)$product_id;
        $sql = "SELECT p.*, c.cat_name, b.brand_name FROM products p 
                LEFT JOIN categories c ON p.product_cat = c.cat_id 
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                WHERE p.product_id = $product_id LIMIT 1";
        return $this->db_fetch_one($sql);
    }

    /**
     * View all products with pagination support
     * @param int $limit - Number of products per page (default: 10)
     * @param int $offset - Offset for pagination (default: 0)
     * @return array|false - Array of products or false on error
     */
    public function view_all_products($limit = 10, $offset = 0)
    {
        $limit = (int)$limit;
        $offset = (int)$offset;
        $sql = "SELECT p.*, c.cat_name, b.brand_name,
                COALESCE(p.rating_average, 0) as rating_average,
                COALESCE(p.rating_count, 0) as rating_count
                FROM products p 
                LEFT JOIN categories c ON p.product_cat = c.cat_id 
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                ORDER BY p.product_id DESC
                LIMIT $limit OFFSET $offset";
        return $this->db_fetch_all($sql);
    }

    /**
     * Get total count of all products
     * @return int - Total number of products
     */
    public function count_all_products()
    {
        $sql = "SELECT COUNT(*) as total FROM products";
        $result = $this->db_fetch_one($sql);
        return $result ? (int)$result['total'] : 0;
    }

    /**
     * Search products by query string (searches in title, description, and keywords)
     * @param string $query - Search term
     * @param int $limit - Number of results per page
     * @param int $offset - Offset for pagination
     * @return array|false - Array of matching products or false on error
     */
    public function search_products($query, $limit = 10, $offset = 0)
    {
        $query = $this->esc($query);
        $limit = (int)$limit;
        $offset = (int)$offset;
        
        $sql = "SELECT p.*, c.cat_name, b.brand_name,
                COALESCE(p.rating_average, 0) as rating_average,
                COALESCE(p.rating_count, 0) as rating_count
                FROM products p 
                LEFT JOIN categories c ON p.product_cat = c.cat_id 
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                WHERE p.product_title LIKE '%$query%' 
                   OR p.product_desc LIKE '%$query%' 
                   OR p.product_keywords LIKE '%$query%'
                ORDER BY p.product_id DESC
                LIMIT $limit OFFSET $offset";
        return $this->db_fetch_all($sql);
    }

    /**
     * Count search results
     * @param string $query - Search term
     * @return int - Total number of matching products
     */
    public function count_search_results($query)
    {
        $query = $this->esc($query);
        $sql = "SELECT COUNT(*) as total FROM products p 
                WHERE p.product_title LIKE '%$query%' 
                   OR p.product_desc LIKE '%$query%' 
                   OR p.product_keywords LIKE '%$query%'";
        $result = $this->db_fetch_one($sql);
        return $result ? (int)$result['total'] : 0;
    }

    /**
     * Filter products by category
     * @param int $cat_id - Category ID
     * @param int $limit - Number of results per page
     * @param int $offset - Offset for pagination
     * @return array|false - Array of products in the category or false on error
     */
    public function filter_products_by_category($cat_id, $limit = 10, $offset = 0)
    {
        $cat_id = (int)$cat_id;
        $limit = (int)$limit;
        $offset = (int)$offset;
        
        $sql = "SELECT p.*, c.cat_name, b.brand_name,
                COALESCE(p.rating_average, 0) as rating_average,
                COALESCE(p.rating_count, 0) as rating_count
                FROM products p 
                LEFT JOIN categories c ON p.product_cat = c.cat_id 
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                WHERE p.product_cat = $cat_id
                ORDER BY p.product_id DESC
                LIMIT $limit OFFSET $offset";
        return $this->db_fetch_all($sql);
    }

    /**
     * Count products by category
     * @param int $cat_id - Category ID
     * @return int - Total number of products in category
     */
    public function count_products_by_category($cat_id)
    {
        $cat_id = (int)$cat_id;
        $sql = "SELECT COUNT(*) as total FROM products WHERE product_cat = $cat_id";
        $result = $this->db_fetch_one($sql);
        return $result ? (int)$result['total'] : 0;
    }

    /**
     * Filter products by brand
     * @param int $brand_id - Brand ID
     * @param int $limit - Number of results per page
     * @param int $offset - Offset for pagination
     * @return array|false - Array of products of the brand or false on error
     */
    public function filter_products_by_brand($brand_id, $limit = 10, $offset = 0)
    {
        $brand_id = (int)$brand_id;
        $limit = (int)$limit;
        $offset = (int)$offset;
        
        $sql = "SELECT p.*, c.cat_name, b.brand_name,
                COALESCE(p.rating_average, 0) as rating_average,
                COALESCE(p.rating_count, 0) as rating_count
                FROM products p 
                LEFT JOIN categories c ON p.product_cat = c.cat_id 
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                WHERE p.product_brand = $brand_id
                ORDER BY p.product_id DESC
                LIMIT $limit OFFSET $offset";
        return $this->db_fetch_all($sql);
    }

    /**
     * Count products by brand
     * @param int $brand_id - Brand ID
     * @return int - Total number of products of the brand
     */
    public function count_products_by_brand($brand_id)
    {
        $brand_id = (int)$brand_id;
        $sql = "SELECT COUNT(*) as total FROM products WHERE product_brand = $brand_id";
        $result = $this->db_fetch_one($sql);
        return $result ? (int)$result['total'] : 0;
    }

    /**
     * View a single product (alias for getProductById for consistency)
     * @param int $id - Product ID
     * @return array|false - Product details or false on error
     */
    public function view_single_product($id)
    {
        return $this->getProductById($id);
    }

    /**
     * Advanced search with multiple filters
     * @param array $filters - Array containing 'query', 'category', 'brand', 'min_price', 'max_price'
     * @param int $limit - Number of results per page
     * @param int $offset - Offset for pagination
     * @return array|false - Array of matching products or false on error
     */
    public function advanced_search($filters, $limit = 10, $offset = 0)
    {
        $limit = (int)$limit;
        $offset = (int)$offset;
        
        $where = [];
        
        if (!empty($filters['query'])) {
            $query = $this->esc($filters['query']);
            $where[] = "(p.product_title LIKE '%$query%' OR p.product_desc LIKE '%$query%' OR p.product_keywords LIKE '%$query%')";
        }
        
        if (!empty($filters['category'])) {
            $cat_id = (int)$filters['category'];
            $where[] = "p.product_cat = $cat_id";
        }
        
        if (!empty($filters['brand'])) {
            $brand_id = (int)$filters['brand'];
            $where[] = "p.product_brand = $brand_id";
        }
        
        if (!empty($filters['min_price'])) {
            $min_price = (float)$filters['min_price'];
            $where[] = "p.product_price >= $min_price";
        }
        
        if (!empty($filters['max_price'])) {
            $max_price = (float)$filters['max_price'];
            $where[] = "p.product_price <= $max_price";
        }
        
        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT p.*, c.cat_name, b.brand_name FROM products p 
                LEFT JOIN categories c ON p.product_cat = c.cat_id 
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                $where_clause
                ORDER BY p.product_id DESC
                LIMIT $limit OFFSET $offset";
        
        return $this->db_fetch_all($sql);
    }

    /**
     * Count results for advanced search
     * @param array $filters - Same filters as advanced_search
     * @return int - Total number of matching products
     */
    public function count_advanced_search($filters)
    {
        $where = [];
        
        if (!empty($filters['query'])) {
            $query = $this->esc($filters['query']);
            $where[] = "(p.product_title LIKE '%$query%' OR p.product_desc LIKE '%$query%' OR p.product_keywords LIKE '%$query%')";
        }
        
        if (!empty($filters['category'])) {
            $cat_id = (int)$filters['category'];
            $where[] = "p.product_cat = $cat_id";
        }
        
        if (!empty($filters['brand'])) {
            $brand_id = (int)$filters['brand'];
            $where[] = "p.product_brand = $brand_id";
        }
        
        if (!empty($filters['min_price'])) {
            $min_price = (float)$filters['min_price'];
            $where[] = "p.product_price >= $min_price";
        }
        
        if (!empty($filters['max_price'])) {
            $max_price = (float)$filters['max_price'];
            $where[] = "p.product_price <= $max_price";
        }
        
        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT COUNT(*) as total FROM products p $where_clause";
        $result = $this->db_fetch_one($sql);
        return $result ? (int)$result['total'] : 0;
    }

    /**
     * Return last DB error message (for debugging)
     */
    public function getLastError()
    {
        if ($this->db == null) {
            $this->db_connect();
        }
        return $this->db ? mysqli_error($this->db) : 'no connection';
    }

    public function getLastQuery()
    {
        return $this->last_query;
    }

    // Get products by seller
    public function getProductsBySeller($seller_id)
    {
        $conn = $this->db_connect();
        if (!$conn) return [];
        
        $seller_id = (int)$seller_id;
        
        $sql = "SELECT p.*, c.cat_name, b.brand_name 
                FROM products p
                LEFT JOIN categories c ON p.product_cat = c.cat_id
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                WHERE p.seller_id = $seller_id
                ORDER BY p.product_id DESC";
        
        return $this->db_fetch_all($sql);
    }

    /**
     * Filter products by type (fabric or service)
     * @param string $type - Product type ('fabric' or 'service')
     * @param int $limit - Number of results per page
     * @param int $offset - Offset for pagination
     * @return array|false - Array of products or false on error
     */
    public function filter_products_by_type($type, $limit = 10, $offset = 0)
    {
        $type = $this->esc($type);
        $limit = (int)$limit;
        $offset = (int)$offset;
        
        $sql = "SELECT p.*, c.cat_name, b.brand_name,
                COALESCE(p.rating_average, 0) as rating_average,
                COALESCE(p.rating_count, 0) as rating_count
                FROM products p 
                LEFT JOIN categories c ON p.product_cat = c.cat_id 
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                WHERE p.product_type = '$type'
                ORDER BY p.product_id DESC
                LIMIT $limit OFFSET $offset";
        return $this->db_fetch_all($sql);
    }

    /**
     * Count products by type
     * @param string $type - Product type ('fabric' or 'service')
     * @return int - Total number of products of that type
     */
    public function count_products_by_type($type)
    {
        $type = $this->esc($type);
        $sql = "SELECT COUNT(*) as total FROM products WHERE product_type = '$type'";
        $result = $this->db_fetch_one($sql);
        return $result ? (int)$result['total'] : 0;
    }

    /**
     * Reduce product stock when order is placed
     */
    public function reduceStock($product_id, $quantity)
    {
        if (!$this->db_connect()) {
            return false;
        }
        
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;
        
        $sql = "UPDATE products 
                SET product_stock = product_stock - $quantity 
                WHERE product_id = $product_id 
                AND product_stock >= $quantity";
        
        return $this->db_write_query($sql);
    }

    /**
     * Check if product has enough stock
     */
    public function checkStock($product_id, $quantity)
    {
        if (!$this->db_connect()) {
            return false;
        }
        
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;
        
        $sql = "SELECT product_stock FROM products WHERE product_id = $product_id";
        $result = $this->db_fetch_one($sql);
        
        if ($result && $result['product_stock'] >= $quantity) {
            return true;
        }
        
        return false;
    }

}

?>