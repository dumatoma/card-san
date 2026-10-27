<?php
// [カスタムエイド 2026-10-26] 予約ウェブサイトの横スライド・直し(2回目)
//   1回目(customaid_reserve_slide_patch.php)が効かなかった原因:
//     ① スライドの終わりを「画面の中のどれかの動きが終わったとき」で判断していた。予約内容の確認などの画面には、
//        uni-app が自動で入れる一瞬(0.001秒)の動きがあり、その終わりでスライドの指定をすぐ消していた → スライドしないで出ていた
//     ② iPhone の Safari が、以前の reserve/index.html を覚えたまま使うことがある(サーバーが「毎回確かめて」と指示していない)
//   直し:
//     ① スライドの終わりは「このスライドそのものが終わったとき」だけで判断する(念のため 0.5秒で必ず片付ける)
//     ② reserve/index.html に「毎回サーバーに確かめる(no-cache)」を付ける(nginx の追加設定。本体の設定は書き換えない)
//     速さ・動き:Card-San App の画面の移り方(iPhone 標準)に近い 0.3秒・最初速く最後ゆっくり
// 使い方:
//   確認だけ:php -d disable_functions= /root/customaid_reserve_slide2_patch.php
//   反映    :php -d disable_functions= /root/customaid_reserve_slide2_patch.php apply
$apply = ($argv[1] ?? '') === 'apply';
$f = '/www/wwwroot/order.card-san.jp/reserve/index.html';
$nginx = '/www/server/nginx/sbin/nginx';
$vhost = '/www/server/panel/vhost/nginx/';
$extDir = $vhost . 'extension/order.card-san.jp/';
$conf = $extDir . 'customaid_reserve_nocache.conf';
$mark = 'customaid-slide-20261026b';
$ok = true;
function say($s) { echo $s, "\n"; }
function ng($s) { global $ok; $ok = false; echo "✕ ", $s, "\n"; }
function sh($c) { $o = []; exec($c . ' 2>&1', $o, $rc); return [$rc, implode("\n", $o)]; }
say($apply ? '=== 反映します ===' : '=== 確認だけ(何も変更しません) ===');

// 1. index.html
$t = @file_get_contents($f);
if ($t === false) { ng("$f がありません"); exit(1); }
$block = <<<HTML
<!-- [カスタムエイド 2026-10-26] $mark:次の画面へ進むとき、右から横にスライドして入る(戻るときは動かさない)。Card-San App と同じ 0.3秒 -->
<style id="$mark">
html,body{overflow-x:hidden}
@keyframes csSlideIn{from{transform:translateX(100%)}to{transform:translateX(0)}}
uni-page.cs-slide-in{animation:csSlideIn .3s cubic-bezier(.2,.7,.3,1) both;box-shadow:-8px 0 24px rgba(0,0,0,.08)}
</style>
<script>
(function(){
  var back=false,last=null,first=true,timer=null;
  window.addEventListener('popstate',function(){back=true;});
  function done(p){if(p)p.classList.remove('cs-slide-in');}
  function check(){
    var ps=document.querySelectorAll('uni-page'),cur=null;
    for(var i=0;i<ps.length;i++){if(ps[i].offsetWidth>0||ps[i].offsetHeight>0||ps[i].getClientRects().length){cur=ps[i];}}
    if(!cur||cur===last)return;
    var forward=!back&&!first;
    if(last)done(last);
    last=cur;first=false;back=false;
    if(!forward)return;
    cur.classList.remove('cs-slide-in');void cur.offsetWidth;cur.classList.add('cs-slide-in');
    var p=cur;
    var h=function(e){if(e.target!==p||e.animationName!=='csSlideIn')return;p.removeEventListener('animationend',h);done(p);};
    p.addEventListener('animationend',h);
    clearTimeout(timer);timer=setTimeout(function(){p.removeEventListener('animationend',h);done(p);},500);
  }
  function start(){new MutationObserver(check).observe(document.body,{childList:true,subtree:true,attributes:true,attributeFilter:['style']});check();}
  if(document.body)start();else document.addEventListener('DOMContentLoaded',start);
})();
</script>
HTML;
$n = $t;
$n = preg_replace('#<!-- \[カスタムエイド 2026-10-26\] customaid-slide-20261026[ab]?:.*?</script>\n?#su', '', $n, -1, $removed);
if (substr_count($n, '</head>') !== 1) ng('index.html の </head> が1つではありません');
$n = str_replace('</head>', $block . "\n</head>", $n);
say("○ $f:以前のスライドの指定 {$removed} か所を取り除き、直した指定を入れる");

// 2. nginx:index.html を毎回確かめる
$main = glob($vhost . '*order.card-san.jp*.conf');
$mainT = $main ? file_get_contents($main[0]) : '';
$root = preg_match('/^\s*root\s+([^;]+);/m', $mainT, $m) ? rtrim(trim($m[1]), '/') : '';
$incl = strpos($mainT, 'extension/order.card-san.jp') !== false;
say('○ order.card-san.jp の設定ファイル:' . ($main ? $main[0] : '見つかりません') . " / root=$root / 追加設定の読み込み:" . ($incl ? 'あり' : 'なし'));
$doNginx = $main && $incl && $root === '/www/wwwroot/order.card-san.jp';
if (!$doNginx) say('△ nginx の追加設定は、想定と違うのでしません(index.html だけ直します。iPhone は Safari の再読み込みで新しくなります)');
$cT = "# [カスタムエイド 2026-10-26] 予約ウェブサイトの入口(index.html)を、毎回サーバーに確かめる(古いものを覚えたまま使わない)\n"
    . "location = /reserve/index.html {\n    add_header Cache-Control \"no-cache\";\n}\n";
if ($doNginx) {
    say(is_file($conf) ? "○ $conf を上書き" : "○ $conf を作る");
    list($rc, $o) = sh($nginx . ' -t');
    if ($rc) ng("今の nginx の設定にエラーがあります: $o"); else say('○ 今の nginx の設定は正常');
}

if (!$ok) { say("\n問題があるので止めます。何も変更していません。"); exit(1); }
if (!$apply) { say("\n確認だけ終わりました。問題がなければ、最後に apply を付けて実行してください。"); exit(0); }

// ===== 反映 =====
$bk = '/www/backup_customaid_reserve_slide2_' . date('Ymd_His') . '/';
mkdir($bk, 0700, true);
copy($f, $bk . 'index.html');
$st = stat($f);
file_put_contents($f, $n);
@chown($f, $st['uid']); @chgrp($f, $st['gid']); @chmod($f, $st['mode'] & 0777);
say("○ index.html を直しました。控え:$bk");
if ($doNginx) {
    @mkdir($extDir, 0755, true);
    if (is_file($conf)) copy($conf, $bk . basename($conf));
    file_put_contents($conf, $cT);
    list($rc, $o) = sh($nginx . ' -t');
    if ($rc) {
        if (is_file($bk . basename($conf))) copy($bk . basename($conf), $conf); else @unlink($conf);
        say("✕ nginx の設定のエラーのため、追加の設定は元に戻しました(index.html は直してあります): $o");
    } else {
        list($rc, $o) = sh($nginx . ' -s reload');
        say($rc ? "✕ nginx の読み直しに失敗: $o" : '○ nginx の追加設定を入れて読み直しました');
    }
}
say("\n確かめ方:iPhone の Safari で https://order.card-san.jp/reserve/?id=11 を開き、ページを1回再読み込みしてから、メニュー → 日時 → 確認 → 予約完了 と進む");
say("元に戻すとき:\\cp {$bk}index.html $f" . ($doNginx ? " ; rm $conf ; $nginx -s reload" : ''));
