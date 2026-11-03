{{-- [カスタムエイド 2026-10-29] 予約メールの HTML(段階5)。中身は SMS のリンク先ページ(cs_sms_page)と同じ。メールソフト向けに表と行の中の書き方で作る --}}
@php
    $T = ['confirm' => ['✓', '予約を受け付けました', 'ご予約の内容', '#2B8A87', '#E3F1F0'], 'notice' => ['!', '明日のご予約', 'ご予約の内容', '#2C6BD6', '#E8F0FC'],
          'update' => ['↻', '予約の内容が変わりました', '変更後のご予約', '#B46A00', '#FFF4E5'], 'cancel' => ['×', 'キャンセルしました', 'キャンセルしたご予約', '#8E8E93', '#EFEFF2']];
    $t = $T[$p['type'] ?? 'confirm'] ?? $T['confirm']; $cx = ($p['type'] ?? '') === 'cancel';
    $shop = $p['shop'] ?? ['name' => '', 'phone' => '', 'address' => '', 'sid' => 0];
    $tel = preg_replace('/[^0-9+]/', '', $shop['phone'] ?? '');
    $box = 'background:#ffffff;border-radius:14px;';
    $f = 'font-family:-apple-system,BlinkMacSystemFont,\'Hiragino Sans\',\'Hiragino Kaku Gothic ProN\',\'Noto Sans JP\',Meiryo,sans-serif;';
    $bt = trim((($p['mid'] ?? '') !== '' ? $p['mid'] . "\n\n" : '') . ($p['bottom'] ?? ''));
@endphp
<!doctype html>
<html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $shop['name'] }}</title></head>
<body style="margin:0;padding:0;background:#F5F5F7;{{ $f }}color:#1D1D1F;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F5F5F7;"><tr><td align="center" style="padding:16px 12px 24px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;">
  <tr><td align="center" style="font-size:18px;font-weight:bold;padding:4px 0 10px;{{ $f }}">{{ $shop['name'] }}</td></tr>
  @if (!empty($p['card']))<tr><td align="center" style="padding:0 0 14px;"><img src="{{ $p['card'] }}" width="180" height="109" alt="{{ $shop['name'] }}のカード" style="display:block;width:180px;height:109px;border-radius:10px;object-fit:cover;border:0;"></td></tr>@endif
  <tr><td style="{{ $box }}padding:20px 14px 16px;" align="center">
    <table role="presentation" cellpadding="0" cellspacing="0"><tr><td align="center" valign="middle" width="44" height="44" style="width:44px;height:44px;border-radius:22px;background:{{ $t[3] }};color:#ffffff;font-size:22px;font-weight:bold;{{ $f }}">{{ $t[0] }}</td></tr></table>
    <div style="margin-top:8px;"><span style="display:inline-block;font-size:12px;font-weight:bold;padding:3px 10px;border-radius:999px;background:{{ $t[4] }};color:{{ $t[3] }};">{{ $t[1] }}</span></div>
    @if (($p['heading'] ?? '') !== '')<div style="margin-top:6px;font-size:14px;font-weight:bold;color:#6E6E73;">{{ $p['heading'] }}</div>@endif
    <div style="margin-top:6px;font-size:21px;font-weight:bold;">{{ $p['name'] ?? '' }}<span style="font-size:15px;font-weight:normal;margin-left:6px;">{{ $p['honor'] ?? '' }}</span></div>
  </td></tr>
  @if (($p['top'] ?? '') !== '')<tr><td style="height:14px;"></td></tr><tr><td style="{{ $box }}padding:14px;font-size:15px;line-height:1.6;">{!! nl2br(e($p['top'])) !!}</td></tr>@endif
  <tr><td style="font-size:13px;color:#6E6E73;padding:14px 6px 6px;">{{ $t[2] }}</td></tr>
  <tr><td style="{{ $box }}padding:0 14px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
    @foreach (($p['rows'] ?? []) as $i => $r)
      <tr>
        <td width="76" valign="top" style="width:76px;padding:12px 0;font-size:13px;color:#6E6E73;{{ $i ? 'border-top:1px solid #E5E5EA;' : '' }}">{{ $r[0] }}</td>
        <td valign="top" style="padding:12px 0;font-size:{{ $r[0] === '日時' ? '16px;font-weight:bold' : '15px' }};color:{{ $cx ? '#8E8E93' : '#1D1D1F' }};{{ $i ? 'border-top:1px solid #E5E5EA;' : '' }}word-break:break-all;"><span style="{{ $cx && $r[0] === '日時' ? 'text-decoration:line-through;' : '' }}">{{ $r[1] }}</span>@if (!empty($r[2]))<br><span style="color:#D93025;font-weight:bold;font-size:14px;">{{ $r[2] }}</span>@endif</td>
        <td width="64" align="right" valign="middle" style="padding:12px 0;{{ $i ? 'border-top:1px solid #E5E5EA;' : '' }}">@if (!empty($r[3]))<img src="{{ $r[3] }}" width="64" height="64" alt="メニューの写真" style="display:block;width:64px;height:64px;border-radius:10px;object-fit:cover;border:0;">@endif</td>
      </tr>
    @endforeach
    </table>
  </td></tr>
  @if ($bt !== '')<tr><td style="height:14px;"></td></tr><tr><td style="{{ $box }}padding:14px;font-size:15px;line-height:1.6;">{!! nl2br(e($bt)) !!}</td></tr>@endif
  <tr><td style="height:14px;"></td></tr>
  <tr><td style="{{ $box }}padding:0 14px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
      <tr><td width="76" style="width:76px;padding:12px 0;font-size:13px;color:#6E6E73;">店舗名</td><td style="padding:12px 0;font-size:15px;">{{ $shop['name'] }}</td></tr>
      @if (($shop['phone'] ?? '') !== '')<tr><td style="padding:12px 0;font-size:13px;color:#6E6E73;border-top:1px solid #E5E5EA;">電話</td><td style="padding:12px 0;font-size:15px;border-top:1px solid #E5E5EA;"><a href="tel:{{ $tel }}" style="color:#1D1D1F;text-decoration:none;">{{ $shop['phone'] }}</a></td></tr>@endif
      @if (($shop['address'] ?? '') !== '')<tr><td style="padding:12px 0;font-size:13px;color:#6E6E73;border-top:1px solid #E5E5EA;">住所</td><td style="padding:12px 0;font-size:15px;border-top:1px solid #E5E5EA;"><a href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($shop['address']) }}" style="color:#2C6BD6;">{{ $shop['address'] }}</a></td></tr>@endif
    </table>
  </td></tr>
  <tr><td style="height:28px;"></td></tr>
  @if ($cx)<tr><td><a href="https://order.card-san.jp/reserve/?id={{ (int) ($shop['sid'] ?? 0) }}" style="display:block;text-align:center;padding:14px;border-radius:12px;background:#2B8A87;color:#ffffff;font-weight:bold;text-decoration:none;font-size:15px;">もう一度予約する</a></td></tr><tr><td style="height:10px;"></td></tr>@endif
  @if ($tel !== '')<tr><td><a href="tel:{{ $tel }}" style="display:block;text-align:center;padding:14px;border-radius:12px;font-weight:bold;text-decoration:none;font-size:15px;{{ $cx ? 'background:#ffffff;color:#1D1D1F;border:1px solid #D2D2D7;' : 'background:#2B8A87;color:#ffffff;' }}">お店に電話する</a></td></tr>@endif
  <tr><td align="center" style="padding:18px 0 0;font-size:12px;line-height:1.7;color:#6E6E73;">このメールは送信専用のアドレスから送っています。<br>ご返信いただいてもお店には届きません。@if ($url !== '')<br>表示が崩れるときは <a href="{{ $url }}" style="color:#2C6BD6;">こちら</a> からご覧ください。@endif<br>Card-San</td></tr>
</table>
</td></tr></table>
</body></html>
