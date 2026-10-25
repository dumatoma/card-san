// [カスタムエイド 2026-10-24] iPhone のファイルアプリの選択画面で PDF を1つ選び、アプリの一時フォルダにコピーして返す
import UIKit
import UniformTypeIdentifiers

public class CardsanFilePicker: NSObject, UIDocumentPickerDelegate {
    // 選択画面を開いている間、この部品が消えないように持っておく
    static var current: CardsanFilePicker? = nil
    var callback: ((String, String, String, String) -> Void)? = nil

    // callback(状態 "ok"/"cancel"/"error", 場所, 元のファイル名, 大きさ または エラーの内容)
    public static func pick(_ callback: @escaping (String, String, String, String) -> Void) {
        DispatchQueue.main.async {
            guard let vc = topViewController() else {
                callback("error", "", "", "画面を開けませんでした")
                return
            }
            let p = CardsanFilePicker()
            p.callback = callback
            current = p
            let picker: UIDocumentPickerViewController
            if #available(iOS 14.0, *) {
                picker = UIDocumentPickerViewController(forOpeningContentTypes: [UTType.pdf], asCopy: true)
            } else {
                picker = UIDocumentPickerViewController(documentTypes: ["com.adobe.pdf"], in: .import)
            }
            picker.delegate = p
            picker.allowsMultipleSelection = false
            vc.present(picker, animated: true, completion: nil)
        }
    }

    static func topViewController() -> UIViewController? {
        var root: UIViewController? = nil
        if #available(iOS 13.0, *) {
            for scene in UIApplication.shared.connectedScenes {
                if let ws = scene as? UIWindowScene {
                    for w in ws.windows where w.isKeyWindow { root = w.rootViewController }
                }
            }
        }
        if root == nil { root = UIApplication.shared.windows.first(where: { $0.isKeyWindow })?.rootViewController }
        if root == nil { root = UIApplication.shared.windows.first?.rootViewController }
        while let presented = root?.presentedViewController { root = presented }
        return root
    }

    public func documentPicker(_ controller: UIDocumentPickerViewController, didPickDocumentsAt urls: [URL]) {
        guard let url = urls.first else {
            finish("cancel", "", "", "")
            return
        }
        let access = url.startAccessingSecurityScopedResource()
        defer { if access { url.stopAccessingSecurityScopedResource() } }
        let fm = FileManager.default
        let dir = fm.temporaryDirectory.appendingPathComponent("karte_pdf", isDirectory: true)
        try? fm.createDirectory(at: dir, withIntermediateDirectories: true, attributes: nil)
        let dest = dir.appendingPathComponent("\(Int(Date().timeIntervalSince1970 * 1000)).pdf")
        do {
            try fm.copyItem(at: url, to: dest)
            var size = 0
            if let attrs = try? fm.attributesOfItem(atPath: dest.path), let n = attrs[.size] as? NSNumber {
                size = n.intValue
            }
            finish("ok", dest.path, url.lastPathComponent, String(size))
        } catch {
            finish("error", "", "", error.localizedDescription)
        }
    }

    public func documentPickerWasCancelled(_ controller: UIDocumentPickerViewController) {
        finish("cancel", "", "", "")
    }

    func finish(_ status: String, _ path: String, _ name: String, _ extra: String) {
        let cb = callback
        callback = nil
        CardsanFilePicker.current = nil
        cb?(status, path, name, extra)
    }
}
