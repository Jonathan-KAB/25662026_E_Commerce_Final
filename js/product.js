// Consolidated product management JS
$(function() {
    var allBrands = [];

    function loadCategories() {
        return $.ajax({ url: '../actions/fetch_category_action.php', method: 'GET', dataType: 'json' });
    }

    function loadBrands() {
        return $.ajax({ url: '../actions/fetch_brand_action.php', method: 'GET', dataType: 'json' });
    }

    function fetchProducts() {
        // add a timestamp to prevent cached responses and force fresh data
        var url = '../actions/fetch_product_action.php?t=' + Date.now();
        $.ajax({ url: url, method: 'GET', dataType: 'json', cache: false })
        .done(function(data) {
            var html = '';
            if (Array.isArray(data) && data.length) {
                data.forEach(function(p) {
                    html += '<div class="card">'
                        + '<div class="card-body">'
                        + '<h6 class="card-title">' + $('<div>').text(p.product_title).html() + '</h6>'
                        + '<p class="text-muted small" style="margin-bottom: 4px;">' + (p.cat_name||'') + ' / ' + (p.brand_name||'') + '</p>'
                        + '<p class="text-muted" style="font-size: 0.875rem; margin-bottom: 10px;">' + $('<div>').text(p.product_desc||'').html() + '</p>'
                        + '<button class="btn btn-sm btn-primary edit-product" data-id="' + p.product_id + '">Edit</button>'
                        + '</div>'
                        + '</div>';
                });
            } else if (data && data.status && data.status === 'error') {
                html = '<div class="col-12 text-danger">' + $('<div>').text(data.message || 'Error fetching products').html() + '</div>';
            } else {
                html = '<div class="col-12 text-muted">No products found.</div>';
            }
            $('#products-container').html(html);
        })
        .fail(function() {
            $('#products-container').html('<div class="col-12 text-danger">Failed to fetch products.</div>');
        });
    }

    // Initialize category and brand selects
    $.when(loadCategories(), loadBrands()).done(function(catsResp, brandsResp) {
        var cats = catsResp[0] || [];
        var brands = brandsResp[0] || [];

        var catHtml = '<option value="">Select category</option>';
        cats.forEach(function(c){ catHtml += '<option value="'+c.cat_id+'">'+$('<div>').text(c.cat_name).html()+'</option>'; });
        $('#product_cat').html(catHtml);

        allBrands = Array.isArray(brands) ? brands : [];

        function populateBrandsForCategory(catId) {
            var html = '<option value="">Select brand</option>';
            if (catId && allBrands.length) {
                allBrands.forEach(function(b){
                    var bcat = b.brand_cat || b.cat_id || b.category_id || 0;
                    if (parseInt(bcat) === parseInt(catId)) {
                        html += '<option value="'+(b.brand_id)+'">'+$('<div>').text(b.brand_name).html()+'</option>';
                    }
                });
            }
            $('#product_brand').html(html);
        }

        // category change -> filter brands
        $(document).on('change', '#product_cat', function() { populateBrandsForCategory($(this).val()); });
        populateBrandsForCategory('');
    });

    fetchProducts();

    // Submit handler for create/update
    $('#product-form').submit(function(e) {
        e.preventDefault();
        var id = parseInt($('#product_id').val(),10) || 0;
        var payload = {
            product_cat: $('#product_cat').val(),
            product_brand: $('#product_brand').val(),
            product_title: $('#product_title').val(),
            product_price: $('#product_price').val(),
            product_desc: $('#product_desc').val(),
            product_keywords: $('#product_keywords').val(),
            product_stock: $('#product_stock').val()
        };
        if (!payload.product_cat || !payload.product_brand || !payload.product_title) {
            alert('Category, Brand and Title are required');
            return;
        }

        var $saveBtn = $('#save-product');
        var oldBtnText = $saveBtn.text();
        $saveBtn.prop('disabled', true).text('Saving...');

        if (id === 0) {
            $.post('../actions/add_product_action.php', payload, function(resp) {
                if (resp && resp.status === 'success') {
                    var newId = resp.product_id;
                    var file = $('#product_image')[0].files[0];
                    if (file) {
                        var fd = new FormData(); fd.append('image', file); fd.append('product_id', newId);
                        $.ajax({ url: '../actions/upload_product_image_action.php', method: 'POST', data: fd, contentType: false, processData: false, dataType: 'json' })
                        .always(function() { fetchProducts(); $('#product-form')[0].reset(); $('#product_id').val(0); $('#image-preview').html(''); })
                        .done(function(u) { if (u.status === 'success') alert('Product created'); else alert(u.message||'Image upload failed'); })
                        .fail(function(){ alert('Image upload failed'); });
                    } else {
                        fetchProducts();
                        $('#product-form')[0].reset();
                        $('#product_id').val(0);
                        $('#image-preview').html('');
                        alert('Product created');
                    }
                } else {
                    alert((resp && resp.message) ? resp.message : 'Failed to add product');
                }
            }, 'json')
            .always(function(){ $saveBtn.prop('disabled', false).text(oldBtnText); });
        } else {
            // update
            $.post('../actions/update_product_action.php', $.extend({}, payload, { product_id: id }), function(resp) {
                if (resp && resp.status === 'success') {
                    var file = $('#product_image')[0].files[0];
                    if (file) {
                        var fd = new FormData(); fd.append('image', file); fd.append('product_id', id);
                        $.ajax({ url: '../actions/upload_product_image_action.php', method: 'POST', data: fd, contentType: false, processData: false, dataType: 'json' })
                        .always(function() { fetchProducts(); $('#product-form')[0].reset(); $('#product_id').val(0); $('#image-preview').html(''); $saveBtn.prop('disabled', false).text(oldBtnText); })
                        .done(function(u) { if (u.status === 'success') alert('Product updated'); else alert(u.message||'Image upload failed'); })
                        .fail(function(){ alert('Image upload failed'); });
                    } else {
                        fetchProducts();
                        $('#product-form')[0].reset();
                        $('#product_id').val(0);
                        $('#image-preview').html('');
                        $saveBtn.prop('disabled', false).text(oldBtnText);
                        alert('Product updated');
                    }
                } else {
                    alert((resp && resp.message) ? resp.message : 'Failed to update product');
                    $saveBtn.prop('disabled', false).text(oldBtnText);
                }
            }, 'json')
            .always(function(){ $saveBtn.prop('disabled', false).text(oldBtnText); });
        }
    });

    // edit handler - load product into form
    $(document).on('click', '.edit-product', function() {
        var id = $(this).data('id');
        $.get('../actions/fetch_product_action.php', { id: id }, function(resp) {
            if (resp && resp.status === 'success' && resp.product) {
                var p = resp.product;
                $('#product_id').val(p.product_id);
                $('#product_cat').val(p.product_cat).trigger('change');
                setTimeout(function(){ $('#product_brand').val(p.product_brand); }, 120);
                $('#product_title').val(p.product_title);
                $('#product_price').val(p.product_price);
                $('#product_stock').val(p.product_stock || 0);
                $('#product_desc').val(p.product_desc);
                $('#product_keywords').val(p.product_keywords);
                if (p.product_image) $('#image-preview').html('<img src="../'+p.product_image+'" style="max-width:200px;max-height:150px;object-fit:cover;"/>'); else $('#image-preview').html('');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                alert((resp && resp.message) ? resp.message : 'Failed to load product');
            }
        }, 'json');
    });

    // Brand management UI (if present on page) - keep separate to avoid conflicts
    function fetchBrandsForAdmin() {
        $.ajax({ url: '../actions/fetch_brand_action.php', method: 'GET', dataType: 'json' })
        .done(function(data) {
            var grouped = {};
            if (Array.isArray(data)) data.forEach(function(b){ var cat = b.cat_name || 'Uncategorized'; (grouped[cat] = grouped[cat]||[]).push(b); });
            var html = '';
            Object.keys(grouped).forEach(function(cat) { html += '<div class="col-12"><h5 class="mt-3">' + $('<div>').text(cat).html() + '</h5></div>'; grouped[cat].forEach(function(b) { html += '<div class="col-md-4"><div class="card"><div class="card-body"><h6 class="card-title">' + $('<div>').text(b.brand_name).html() + '</h6><div class="d-flex gap-2"><button class="btn btn-sm btn-warning edit-brand" data-id="' + b.brand_id + '" data-name="' + $('<div>').text(b.brand_name).html() + '">Edit</button><button class="btn btn-sm btn-danger delete-brand" data-id="' + b.brand_id + '">Delete</button></div></div></div></div>'; }); });
            if (html === '') html = '<div class="col-12 text-muted">No brands found.</div>';
            $('#brands-container').html(html);
        })
        .fail(function(){ $('#brands-container').html('<div class="col-12 text-danger">Failed to fetch brands.</div>'); });
    }

    // Hook brand admin actions if present
    $(document).on('submit', '#add-brand-form', function(e){ e.preventDefault(); var brand_name = $('#brand_name').val(); var brand_cat = $('#brand_cat').val(); if (!brand_name||!brand_cat){ alert('Please enter brand and category'); return; } $.post('../actions/add_brand_action.php',{brand_name:brand_name,brand_cat:brand_cat}, function(resp){ if (resp && resp.status==='success'){ $('#brand_name').val(''); $('#brand_cat').val(''); fetchBrandsForAdmin(); } else alert((resp&&resp.message)?resp.message:'Failed to add brand'); }, 'json'); });

    $(document).on('click', '.edit-brand', function(){ var id = $(this).data('id'); var oldName = $(this).data('name'); var newName = prompt('Edit brand name:', oldName); if (newName && newName !== oldName) { $.post('../actions/update_brand_action.php',{brand_id:id,brand_name:newName}, function(resp){ if (resp && resp.status==='success') fetchBrandsForAdmin(); else alert((resp&&resp.message)?resp.message:'Failed to update brand'); }, 'json'); } });

    $(document).on('click', '.delete-brand', function(){ if (!confirm('Are you sure you want to delete this brand?')) return; var id = $(this).data('id'); $.post('../actions/delete_brand_action.php',{brand_id:id}, function(resp){ if (resp && resp.status==='success') fetchBrandsForAdmin(); else alert((resp&&resp.message)?resp.message:'Failed to delete brand'); }, 'json'); });

});