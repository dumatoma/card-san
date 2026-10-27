// [カスタムエイド 2026-10-26] 店舗QR統一:iPhone 標準の「ペースト」ボタン(UIPasteControl)
//   ・ボタンの文字「ペースト」は iPhone が表示する(変えられない)。表示は文字だけ(labelOnly)、地は青、文字は白
//   ・ボタンのすぐ右に矢印「→」を自前で描き、外側の角丸の枠で1つのボタンに見せる(矢印を重ねると貼り付けが無効になるため、横に並べる)
//   ・押すとコピーされている文字を受け取り onPaste に渡す(貼り付けの確認画面は出ない)
//   ・iOS 15 以前は UIPasteControl がないので、何も表示しない(isSupported() が false)
import UIKit

@objc public class CsPasteView: UIView {
    @objc public var onPaste: ((String) -> Void)?
    private let blue = UIColor(red: 10.0 / 255.0, green: 102.0 / 255.0, blue: 214.0 / 255.0, alpha: 1.0)
    private var pasteControl: UIView?
    private let arrow = UILabel()
    private let line = UIView()

    public override init(frame: CGRect) {
        super.init(frame: frame)
        setup()
    }

    required init?(coder: NSCoder) {
        super.init(coder: coder)
        setup()
    }

    @objc public static func isSupported() -> Bool {
        if #available(iOS 16.0, *) { return true }
        return false
    }

    // [カスタムエイド 2026-10-26] 画面に渡す形({ text: 文字 })をここで作る(UTS の { } だと型が合わずビルドが止まったため)
    @objc public static func detail(_ text: String) -> [String: Any] {
        return ["text": text]
    }

    // コピーされている文字があるか(中身は読まないので、確認画面は出ない)
    @objc public static func hasStrings() -> Bool {
        return UIPasteboard.general.hasStrings
    }

    private func setup() {
        backgroundColor = blue
        layer.cornerRadius = 14
        clipsToBounds = true
        guard #available(iOS 16.0, *) else {
            isHidden = true
            return
        }
        pasteConfiguration = UIPasteConfiguration(acceptableTypeIdentifiers: ["public.utf8-plain-text", "public.plain-text", "public.text"])
        let cfg = UIPasteControl.Configuration()
        cfg.displayMode = .labelOnly
        cfg.cornerStyle = .fixed
        cfg.cornerRadius = 0
        cfg.baseBackgroundColor = blue
        cfg.baseForegroundColor = .white
        let pc = UIPasteControl(configuration: cfg)
        pc.target = self
        addSubview(pc)
        pasteControl = pc

        line.backgroundColor = UIColor(white: 1.0, alpha: 0.25)
        addSubview(line)
        arrow.text = "→"
        arrow.textColor = .white
        arrow.textAlignment = .center
        arrow.font = UIFont.systemFont(ofSize: 22, weight: .semibold)
        addSubview(arrow)
    }

    public override func layoutSubviews() {
        super.layoutSubviews()
        let w = bounds.width, h = bounds.height
        let aw: CGFloat = min(56, w * 0.22)
        pasteControl?.frame = CGRect(x: 0, y: 0, width: w - aw, height: h)
        line.frame = CGRect(x: w - aw, y: h * 0.22, width: 1, height: h * 0.56)
        arrow.frame = CGRect(x: w - aw, y: 0, width: aw, height: h)
    }

    // ペーストボタンが押されたとき、iPhone から呼ばれる
    // [カスタムエイド 2026-10-26b] 1回目の確認で「お店の情報が見つかりません」になった(コピーはされていた)ため、受け取り方を増やした
    //   ① 文字(NSString)として受け取る ② だめなら種類を指定して受け取る(文字・データ・URL のどれで来ても文字にする)
    public override func paste(itemProviders: [NSItemProvider]) {
        let types = ["public.utf8-plain-text", "public.plain-text", "public.text", "public.url"]
        var sent = false
        // [カスタムエイド 2026-10-26c] 2回目の確認でも、画面に受け取った文字が届いていなかった(@paste の中に文字がない)。
        //   そこで、受け取った文字と確認用の情報を端末の保存場所(UserDefaults)にも書き、画面からはそこを読む
        let info = itemProviders.map { $0.registeredTypeIdentifiers.joined(separator: ",") }.joined(separator: " / ")
        let send: (String) -> Void = { [weak self] got in
            DispatchQueue.main.async {
                if sent { return }
                sent = true
                var text = got
                var how = "provider"
                // [カスタムエイド 2026-10-26c] 受け取れなかったときの最後の手:ボタンを押した直後なので、コピーされている文字を直接読む
                if text.isEmpty, let v = UIPasteboard.general.string, !v.isEmpty {
                    text = v
                    how = "pasteboard"
                }
                let d = UserDefaults.standard
                d.set(text, forKey: "csPasteText")
                d.set(Date().timeIntervalSince1970, forKey: "csPasteAt")
                d.set("n=\(itemProviders.count) types=\(info) len=\(text.count) by=\(how)", forKey: "csPasteDbg")
                d.synchronize()
                self?.onPaste?(text)
            }
        }
        for p in itemProviders {
            if p.canLoadObject(ofClass: NSString.self) {
                _ = p.loadObject(ofClass: NSString.self) { obj, _ in
                    if let s = obj as? String, !s.isEmpty { send(s); return }
                    if let s = obj as? NSString, s.length > 0 { send(s as String); return }
                    CsPasteView.loadByType(p, types, 0, send)
                }
                return
            }
            if types.contains(where: { p.hasItemConformingToTypeIdentifier($0) }) {
                CsPasteView.loadByType(p, types, 0, send)
                return
            }
        }
        send("")
    }

    private static func loadByType(_ p: NSItemProvider, _ types: [String], _ i: Int, _ send: @escaping (String) -> Void) {
        if i >= types.count { send(""); return }
        let t = types[i]
        if !p.hasItemConformingToTypeIdentifier(t) { loadByType(p, types, i + 1, send); return }
        p.loadItem(forTypeIdentifier: t, options: nil) { item, _ in
            var s = ""
            if let v = item as? String { s = v }
            else if let v = item as? NSString { s = v as String }
            else if let v = item as? Data { s = String(data: v, encoding: .utf8) ?? "" }
            else if let v = item as? URL { s = v.absoluteString }
            else if let v = item as? NSAttributedString { s = v.string }
            if s.isEmpty { loadByType(p, types, i + 1, send) } else { send(s) }
        }
    }

    public override func canPaste(_ itemProviders: [NSItemProvider]) -> Bool {
        return true
    }
}
