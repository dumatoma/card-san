// [カスタムエイド 2026-10-27] 個推(GeTui)をなくす:iPhone の通知を自前で扱う部品(Card-San App・管理App 共通)
//   ・Apple の番号(デバイストークン)を16進の文字で保存する(index.uts の didRegisterForRemoteNotifications から呼ばれる)
//   ・通知のタップ(click)と、アプリを開いているときの受信(receive)を貯めておき、アプリの画面側(App.vue)が take() で取り出す
//   ・UNUserNotificationCenter の受け手(delegate)を置く。もともと別の受け手(DCloud の部品)がいれば、そちらにも必ず渡す
//     (アプリを開いているときの表示は、こちらで必ず出す[10/27b]。タップは、もともとの受け手にも渡す)
import UIKit
import UserNotifications

@objc public class CsPushCore: NSObject, UNUserNotificationCenterDelegate {
    @objc public static let shared = CsPushCore()
    private static let lock = NSLock()
    private static var events: [[String: Any]] = []
    private weak var prev: UNUserNotificationCenterDelegate?
    private var installed = false

    // 受け手を置く(何度呼んでもよい)
    @objc public static func install() {
        let run = {
            let c = UNUserNotificationCenter.current()
            if let d = c.delegate, d === shared { return }
            if let d = c.delegate { shared.prev = d }
            c.delegate = shared
            shared.installed = true
        }
        if Thread.isMainThread { run() } else { DispatchQueue.main.async(execute: run) }
    }

    // 通知の許可を聞いて、Apple に番号を作ってもらう
    @objc public static func register() {
        install()
        UNUserNotificationCenter.current().requestAuthorization(options: [.alert, .badge, .sound]) { _, _ in
            DispatchQueue.main.async { UIApplication.shared.registerForRemoteNotifications() }
        }
    }

    @objc public static func setToken(_ data: Data) {
        let hex = data.map { String(format: "%02x", $0) }.joined()
        UserDefaults.standard.set(hex, forKey: "csApnsToken")
        UserDefaults.standard.synchronize()
    }

    @objc public static func token() -> String {
        return UserDefaults.standard.string(forKey: "csApnsToken") ?? ""
    }

    // 貯まった知らせを JSON の文字で返して空にする
    @objc public static func take() -> String {
        lock.lock()
        let list = events
        events.removeAll()
        lock.unlock()
        if list.isEmpty { return "" }
        guard let d = try? JSONSerialization.data(withJSONObject: list, options: []), let s = String(data: d, encoding: .utf8) else { return "" }
        return s
    }

    private static func add(_ type: String, _ n: UNNotification) {
        let c = n.request.content
        var payload = ""
        if let p = c.userInfo["payload"] as? String { payload = p }
        else if let p = c.userInfo["payload"], JSONSerialization.isValidJSONObject(p), let d = try? JSONSerialization.data(withJSONObject: p, options: []) { payload = String(data: d, encoding: .utf8) ?? "" }
        lock.lock()
        events.append(["type": type, "title": c.title, "content": c.body, "payload": payload, "id": n.request.identifier])
        if events.count > 50 { events.removeFirst(events.count - 50) }
        lock.unlock()
    }

    // アプリを開いているときに届いた
    public func userNotificationCenter(_ center: UNUserNotificationCenter, willPresent notification: UNNotification, withCompletionHandler completionHandler: @escaping (UNNotificationPresentationOptions) -> Void) {
        CsPushCore.add("receive", notification)
        // [カスタムエイド 2026-10-27b] アプリを開いているときは、もともとの受け手(DCloud の部品)に任せず、ここで必ずバナー・音・バッジを出す。
        //   1098・1111 では DCloud の部品に任せていたため、個推がない状態で「表示しない」になり、開いているときに通知が出なかった(坂本さんの確認)
        if #available(iOS 14.0, *) { completionHandler([.banner, .list, .sound, .badge]) } else { completionHandler([.alert, .sound, .badge]) }
    }

    // 通知をタップした(アプリが閉じていたときも)
    public func userNotificationCenter(_ center: UNUserNotificationCenter, didReceive response: UNNotificationResponse, withCompletionHandler completionHandler: @escaping () -> Void) {
        CsPushCore.add("click", response.notification)
        if let p = prev, p.responds(to: #selector(UNUserNotificationCenterDelegate.userNotificationCenter(_:didReceive:withCompletionHandler:))) {
            p.userNotificationCenter?(center, didReceive: response, withCompletionHandler: completionHandler)
            return
        }
        completionHandler()
    }
}
