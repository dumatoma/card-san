{{-- [カスタムエイド 2026-11-03] 見本「Card-San お知らせ見本」どおりのメール。中身は app/Libs/CsShopNotice.php の systemMail で作る --}}
{!! \App\Libs\CsShopNotice::systemMail('shop_register', ['email' => $email ?? '', 'name' => $name ?? '', 'shop_name' => $shop_name ?? '', 'code' => $code ?? '']) !!}
