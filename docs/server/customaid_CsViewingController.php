<?php
// [カスタムエイド 2026-10-27] 今開いているチャットを覚え、その相手からの通知を送らない(LINE と同じ)。アプリが30秒ごとに知らせ、45秒で消える
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
class CsViewingController extends Controller
{
    public static function key($type, $cid) { return 'csview:' . $type . ':' . $cid; }
    private function save($type, $user, Request $r)
    {
        $cid = $user ? (string) ($user->getui_clientid ?? '') : '';
        if ($cid === '') return responseToJson(200);
        $v = substr(preg_replace('/[^a-z_0-9:]/', '', (string) $r->view), 0, 40);
        if ($v === '') Cache::forget(self::key($type, $cid)); else Cache::put(self::key($type, $cid), $v, 45);
        return responseToJson(200);
    }
    public function member(Request $r) { return $this->save('user', auth()->user(), $r); }
    public function shop(Request $r) { return $this->save('shop', auth('shop')->user(), $r); }
    // 個推・Apple に送る前に呼ぶ。見ている相手からの通知だけ、その端末を外す。うまくいかなければ何もしない
    public static function filter($type, $list)
    {
        try {
            $out = [];
            foreach ((array) $list as $m) {
                $p = $m['push_channel']['ios']['payload'] ?? ($m['push_message']['notification']['payload'] ?? null);
                $p = is_string($p) ? json_decode($p, true) : $p; $t = is_array($p) ? ($p['type'] ?? '') : ''; $w = '';
                if ($t === 'message' && isset($p['id'])) $w = 'message:' . $p['id'];
                elseif ($t === 'staff_room') $w = 'staff_room';
                elseif ($t === 'staff_dm' && isset($p['from_id'])) $w = 'staff_dm:' . $p['from_id'];
                if ($w === '') { $out[] = $m; continue; }
                $keep = [];
                foreach ((array) ($m['audience']['cid'] ?? []) as $c) if (!$c || Cache::get(self::key($type, $c)) !== $w) $keep[] = $c;
                if (!$keep) continue;
                $m['audience']['cid'] = $keep; $out[] = $m;
            }
            return $out;
        } catch (\Throwable $e) { return $list; }
    }
}
