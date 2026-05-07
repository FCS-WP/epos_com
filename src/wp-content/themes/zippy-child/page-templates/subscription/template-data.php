<?php

/** @var array{
 *   hero_image: string,
 *   mockup_image: string,
 *   qr_image: string,
 *   delivery_image: string,
 *   delivery_man_image: string,
 *   grow_sales_image: string,
 *   left_merchant_image: string,
 *   right_merchant_image: string,
 *   avatar_1: string,
 *   avatar_2: string,
 *   avatar_3: string,
 *   logo_epos: string,
 *   logo_alipay: string,
 *   logo_tng: string,
 *   logo_duitnow: string,
 *   logo_tng_small: string,
 *   logo_mydebit: string,
 *   logo_visa: string,
 *   logo_mastercard: string,
 *   header_logo: string,
 *   contact_sales_url: string
 * } $sub_v2
 */
if (!isset($sub_v2) || !is_array($sub_v2)) {
  $sub_v2_custom_logo_id = (int) get_theme_mod('custom_logo');
  $sub_v2_header_logo = $sub_v2_custom_logo_id ? wp_get_attachment_image_url($sub_v2_custom_logo_id, 'full') : '';

  $sub_v2 = [
    'hero_image' => '/wp-content/uploads/2026/05/KV-Website-copy-1-1.webp',
    'mockup_image' => '/wp-content/uploads/2026/05/KV-3-copy-1-1.webp',
    'qr_image' => '/wp-content/uploads/2026/05/Layer_1-4.webp',
    'delivery_image' => '/wp-content/uploads/2026/05/Layer_1-6.webp',
    'delivery_man_image' => '/wp-content/uploads/2026/05/Visuals-001-1-1.png',
    'grow_sales_image' => '/wp-content/uploads/2026/05/Layer_1-5.webp',
    'left_merchant_image' => '/wp-content/uploads/2026/05/Layer_1-2-1.webp',
    'right_merchant_image' => '/wp-content/uploads/2026/05/Layer_1-3.webp',
    'avatar_1' => '/wp-content/uploads/2026/05/921c1a.png',
    'avatar_2' => '/wp-content/uploads/2026/05/921c1a.png',
    'avatar_3' => '/wp-content/uploads/2026/05/avatar-3.webp',
    'logo_epos' => '/wp-content/uploads/2026/05/Group-2117133424-1.webp',
    'logo_alipay' => '/wp-content/uploads/2026/05/Isolation_Mode-1.webp',
    'logo_tng' => '/wp-content/uploads/2026/05/Isolation_Mode-5.webp',
    'logo_duitnow' => '/wp-content/uploads/2026/05/Isolation_Mode-6.webp',
    'logo_tng_small' => '/wp-content/uploads/2026/05/Isolation_Mode-5.webp',
    'logo_mydebit' => '/wp-content/uploads/2026/05/Isolation_Mode-4.webp',
    'logo_visa' => '/wp-content/uploads/2026/05/Isolation_Mode-3.webp',
    'logo_mastercard' => '/wp-content/uploads/2026/05/Isolation_Mode-2.webp',
    'header_logo' => $sub_v2_header_logo ?: 'https://www.epos.com.sg/wp-content/uploads/2025/12/EPOS_Full-Color.webp',
    'contact_sales_url' => 'https://api.whatsapp.com/send/?phone=60124655571&text=Hi+there%2C+I+want+to+learn+about+EPOS360&type=phone_number&app_absent=0',
  ];
}