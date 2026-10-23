<?php
if (!defined('ABSPATH')) { exit; }
function lt_contact_values() {
 $value=get_option('lt_contact_details',array());
 return wp_parse_args(is_array($value)?$value:array(),array('email'=>'','phone'=>'','address'=>''));
}
add_action('admin_menu',function(){
 add_theme_page('Liên hệ LongTech','Liên hệ LongTech','manage_options','lt-contact','lt_contact_settings');
});
function lt_contact_settings(){
 if(!current_user_can('manage_options'))return;
 $v=lt_contact_values();
 echo '<div class="wrap"><h1>Liên hệ LongTech</h1><p>Chỉ nhập thông tin bạn muốn hiển thị công khai trên website. Để trống để ẩn từng kênh liên hệ.</p>';
 if(isset($_POST['lt_contact_save'])){
 check_admin_referer('lt_contact_save');
 $email=isset($_POST['lt_email']) && is_string($_POST['lt_email'])?trim(wp_unslash($_POST['lt_email'])):'';
 $phone=isset($_POST['lt_phone']) && is_string($_POST['lt_phone'])?sanitize_text_field(wp_unslash($_POST['lt_phone'])):'';
 $address=isset($_POST['lt_address']) && is_string($_POST['lt_address'])?sanitize_textarea_field(wp_unslash($_POST['lt_address'])):'';
 if(($email!=='' && !is_email($email)) || ($phone!=='' && !preg_match('/^\+?[0-9][0-9 ().-]{5,23}$/',$phone))){
 echo '<div class="notice notice-error"><p>Chưa lưu: kiểm tra địa chỉ email và số điện thoại (chỉ số, dấu + ở đầu, khoảng trắng, dấu chấm, gạch nối hoặc ngoặc).</p></div>';
 }else{
 $v=array('email'=>sanitize_email($email),'phone'=>$phone,'address'=>$address);
 update_option('lt_contact_details',$v);
 echo '<div class="notice notice-success"><p>Đã lưu thông tin liên hệ.</p></div>';
 }
 }
 echo '<form method="post">';wp_nonce_field('lt_contact_save');
 echo '<table class="form-table"><tr><th><label for="lt_email">Email công khai</label></th><td><input class="regular-text" type="email" id="lt_email" name="lt_email" value="'.esc_attr($v['email']).'"><p class="description">Nút email mở ứng dụng soạn thư của khách; website không tự gửi thư.</p></td></tr>';
 echo '<tr><th><label for="lt_phone">Số điện thoại</label></th><td><input class="regular-text" type="tel" id="lt_phone" name="lt_phone" value="'.esc_attr($v['phone']).'"></td></tr>';
 echo '<tr><th><label for="lt_address">Địa chỉ / khu vực</label></th><td><textarea class="large-text" rows="3" id="lt_address" name="lt_address">'.esc_textarea($v['address']).'</textarea></td></tr></table>';
 submit_button('Lưu thông tin liên hệ','primary','lt_contact_save');echo '</form></div>';
}
add_shortcode('longtech_contact',function(){
 $v=lt_contact_values();$out='<div class="lt-contact-channels">';$count=0;
 if($v['email'] && is_email($v['email'])){$count++;$out.='<a class="lt-channel" href="'.esc_url('mailto:'.$v['email']).'"><span class="lt-channel-icon" aria-hidden="true">@</span><span><span class="lt-channel-label">GỬI EMAIL</span><strong>'.esc_html($v['email']).'</strong><small>Mở ứng dụng soạn thư của bạn ↗</small></span></a>';}
 $tel=preg_replace('/[^0-9+]/','',$v['phone']);
 if($tel && preg_match('/^\+?[0-9]+$/',$tel)){$count++;$out.='<a class="lt-channel" href="'.esc_url('tel:'.$tel).'"><span class="lt-channel-icon" aria-hidden="true">↗</span><span><span class="lt-channel-label">ĐIỆN THOẠI</span><strong>'.esc_html($v['phone']).'</strong><small>Kết nối trực tiếp</small></span></a>';}
 if($v['address']){$count++;$out.='<div class="lt-channel"><span class="lt-channel-icon" aria-hidden="true">⌖</span><span><span class="lt-channel-label">KHU VỰC LIÊN HỆ</span><strong>'.nl2br(esc_html($v['address'])).'</strong></span></div>';}
 if(!$count)$out.='<div class="lt-contact-empty"><span class="lt-channel-icon" aria-hidden="true">@</span><h3>Hẹn kết nối cùng bạn.</h3><p>Kênh liên hệ chính thức đang được cập nhật. Trong lúc chờ, hãy khám phá bộ sưu tập thiết bị của LongTech.</p><a class="lt-product-link" href="'.esc_url(home_url('/#bo-suu-tap')).'">Khám phá sản phẩm <span aria-hidden="true">↗</span></a></div>';
 return $out.'</div>';
});
