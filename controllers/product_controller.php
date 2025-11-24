<?php
require_once __DIR__ . '/../classes/product_class.php';

function add_product_ctr($data) {
    $p = new Product();
    return $p->addProduct($data);
}

function update_product_ctr($product_id, $data) {
    $p = new Product();
    return $p->updateProduct($product_id, $data);
}

function fetch_all_products_ctr() {
    $p = new Product();
    return $p->fetchAllProducts();
}

function get_product_ctr($product_id) {
    $p = new Product();
    return $p->getProductById($product_id);
}

// New controller functions for customer-facing product display

function view_all_products_ctr($limit = 10, $offset = 0) {
    $p = new Product();
    return $p->view_all_products($limit, $offset);
}

function count_all_products_ctr() {
    $p = new Product();
    return $p->count_all_products();
}

function search_products_ctr($query, $limit = 10, $offset = 0) {
    $p = new Product();
    return $p->search_products($query, $limit, $offset);
}

function count_search_results_ctr($query) {
    $p = new Product();
    return $p->count_search_results($query);
}

function filter_products_by_category_ctr($cat_id, $limit = 10, $offset = 0) {
    $p = new Product();
    return $p->filter_products_by_category($cat_id, $limit, $offset);
}

function count_products_by_category_ctr($cat_id) {
    $p = new Product();
    return $p->count_products_by_category($cat_id);
}

function filter_products_by_brand_ctr($brand_id, $limit = 10, $offset = 0) {
    $p = new Product();
    return $p->filter_products_by_brand($brand_id, $limit, $offset);
}

function count_products_by_brand_ctr($brand_id) {
    $p = new Product();
    return $p->count_products_by_brand($brand_id);
}

function view_single_product_ctr($id) {
    $p = new Product();
    return $p->view_single_product($id);
}

function advanced_search_ctr($filters, $limit = 10, $offset = 0) {
    $p = new Product();
    return $p->advanced_search($filters, $limit, $offset);
}

function count_advanced_search_ctr($filters) {
    $p = new Product();
    return $p->count_advanced_search($filters);
}

function get_products_by_seller_ctr($seller_id) {
    $p = new Product();
    return $p->getProductsBySeller($seller_id);
}

function filter_products_by_type_ctr($type, $limit = 10, $offset = 0) {
    $p = new Product();
    return $p->filter_products_by_type($type, $limit, $offset);
}

function count_products_by_type_ctr($type) {
    $p = new Product();
    return $p->count_products_by_type($type);
}

/**
 * Reduce product stock
 */
function reduce_product_stock_ctr($product_id, $quantity)
{
    $p = new Product();
    return $p->reduceStock($product_id, $quantity);
}

/**
 * Check product stock availability
 */
function check_product_stock_ctr($product_id, $quantity)
{
    $p = new Product();
    return $p->checkStock($product_id, $quantity);
}

