<?php
if (!defined('ABSPATH')) { exit; }
add_action('init', function () {
 register_post_type('lt_product', array('labels'=>array('name'=>'Sản phẩm LongTech','singular_name'=>'Sản phẩm','add_new_item'=>'Thêm sản phẩm','edit_item'=>'Sửa sản phẩm'),'public'=>true,'show_in_rest'=>true,'menu_icon'=>'dashicons-products','supports'=>array('title','editor','excerpt','thumbnail','revisions'),'rewrite'=>false,'has_archive'=>false));
 register_taxonomy('lt_category','lt_product',array('label'=>'Nhóm sản phẩm','public'=>true,'hierarchical'=>true,'show_in_rest'=>true,'rewrite'=>false));
});
add_action('after_setup_theme',function(){add_theme_support('post-thumbnails');});
function lt_product_picture($id) {
 if(has_post_thumbnail($id)) return get_the_post_thumbnail($id,'large',array('loading'=>'lazy'));
 $key=get_post_meta($id,'_lt_seed',true);
 if(preg_match('/^[a-z0-9-]+$/',$key) && file_exists(get_template_directory().'/assets/'.$key.'.jpg')) return '<img loading="lazy" src="'.esc_url(get_template_directory_uri().'/assets/'.$key.'.jpg').'" alt="'.esc_attr(get_the_title($id)).'">';
 return '<span class="lt-no-image">'.esc_html(get_the_title($id)).'</span>';
}
add_shortcode('longtech_products',function(){
 $out='<section class="lt-catalog" id="bo-suu-tap"><p class="lt-eyebrow">BỘ SƯU TẬP LONGTECH</p><h2>Công nghệ cho mỗi ngày.</h2><p>Khám phá thiết bị cho học tập, làm việc và kết nối.</p>';
 foreach(array('laptop'=>'Laptop','dien-thoai'=>'Điện thoại','phu-kien'=>'Phụ kiện') as $slug=>$label){
 $q=new WP_Query(array('post_type'=>'lt_product','post_status'=>'publish','posts_per_page'=>-1,'orderby'=>'menu_order title','order'=>'ASC','tax_query'=>array(array('taxonomy'=>'lt_category','field'=>'slug','terms'=>$slug))));
 $out.='<div id="'.esc_attr($slug).'" class="lt-product-group"><h3>'.esc_html($label).' <span class="lt-group-count">'.intval($q->post_count).' sản phẩm</span></h3><div class="lt-product-grid">';
 while($q->have_posts()){$q->the_post();$id=get_the_ID();$url=get_permalink($id);
 $out.='<article class="lt-product-card"><a class="lt-product-image" href="'.esc_url($url).'">'.lt_product_picture($id).'</a><div class="lt-product-copy"><span class="lt-eyebrow">'.esc_html($label).'</span><h4><a href="'.esc_url($url).'">'.esc_html(get_the_title()).'</a></h4><p>'.esc_html(get_the_excerpt()).'</p><a class="lt-product-link" href="'.esc_url($url).'">Xem chi tiết <span aria-hidden="true">↗</span></a></div></article>';
 }wp_reset_postdata();
 if(!$q->post_count)$out.='<p>Sản phẩm đang được cập nhật.</p>';
 $out.='</div></div>';
 }return $out.'</section>';
});
add_shortcode('longtech_product_image',function(){return is_singular('lt_product')?'<div class="lt-single-image">'.lt_product_picture(get_the_ID()).'</div>':'';});
add_action('admin_menu',function(){add_submenu_page('edit.php?post_type=lt_product','Nhập bộ sản phẩm','Nhập bộ sản phẩm','manage_options','lt-import','lt_import_page');});
function lt_import_page(){
 if(!current_user_can('manage_options'))return;
 echo '<div class="wrap"><h1>Bộ sản phẩm LongTech</h1><p>Bộ 15 sản phẩm: 5 laptop, 5 điện thoại, 5 phụ kiện. Chỉ thêm các sản phẩm còn thiếu. Bỏ qua sản phẩm đã nhập, không ghi đè nội dung bạn đã sửa. Ảnh minh họa từ hãng được đóng gói cùng giao diện.</p>';
 if(isset($_POST['lt_import'])){
 check_admin_referer('lt_import');$count=0;$errors=0;$skipped=0;
 $rows=json_decode(file_get_contents(get_template_directory().'/products.json'),true);
 if(!is_array($rows)){echo '<div class="notice notice-error"><p>Không đọc được dữ liệu sản phẩm.</p></div></div>';return;}
 foreach($rows as $i=>$r){
 $exists=get_posts(array('post_type'=>'lt_product','post_status'=>array('publish','draft','pending','private','future','trash'),'meta_key'=>'_lt_seed','meta_value'=>$r[0],'fields'=>'ids','posts_per_page'=>1));if($exists){$skipped++;continue;}
 $content='<!-- wp:paragraph --><p>'.esc_html($r[3]).'</p><!-- /wp:paragraph --><!-- wp:heading --><h2 class="wp-block-heading">Thông tin nổi bật</h2><!-- /wp:heading -->';
 foreach(explode(';',$r[4]) as $spec)$content.='<!-- wp:paragraph --><p>'.esc_html($spec).'</p><!-- /wp:paragraph -->';
 $content.='<!-- wp:paragraph --><p>Thông tin tham khảo từ hãng; cấu hình và màu sắc có thể khác theo thị trường. Đây là trang giới thiệu sản phẩm, chưa cung cấp giá bán.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p><a href="'.esc_url($r[5]).'" target="_blank" rel="noopener noreferrer">Xem thông tin từ nhà sản xuất ↗</a></p><!-- /wp:paragraph -->';
 $id=wp_insert_post(array('post_type'=>'lt_product','post_status'=>'publish','post_title'=>$r[1],'post_name'=>$r[0],'post_excerpt'=>$r[3],'post_content'=>$content,'menu_order'=>$i,'meta_input'=>array('_lt_seed'=>$r[0])),true);
 if(is_wp_error($id)){$errors++;continue;}
 $labels=array('laptop'=>'Laptop','dien-thoai'=>'Điện thoại','phu-kien'=>'Phụ kiện');
 $term=term_exists($r[2],'lt_category');if(!$term)$term=wp_insert_term($labels[$r[2]],'lt_category',array('slug'=>$r[2]));
 if(!is_wp_error($term))wp_set_object_terms($id,array((int)$term['term_id']),'lt_category');else $errors++;
 $count++;
 }
 echo '<div class="notice notice-success"><p>Đã thêm '.intval($count).' sản phẩm. Bỏ qua: '.intval($skipped).'. Lỗi: '.intval($errors).'.</p></div>';
 }
 echo '<form method="post">';wp_nonce_field('lt_import');submit_button('Bổ sung bộ 15 sản phẩm','primary','lt_import');echo '</form><p>Sửa nội dung tại Sản phẩm LongTech → Tất cả sản phẩm. Dùng Ảnh đại diện để thay ảnh mặc định.</p></div>';
}
