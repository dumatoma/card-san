// [カスタムエイド 2026-10-24] Android:選んだ PDF(content://)をアプリの一時フォルダにコピーする
// 一部の機種では content:// のままだと読めないため、必ずコピーしてから送る
package uts.sdk.modules.cardsanFilePicker

import android.content.Context
import android.net.Uri
import android.provider.OpenableColumns
import java.io.File

object CardsanPdfCopy {
    // 戻り値:「状態\t場所\t元のファイル名\t大きさ または エラーの内容」
    @JvmStatic
    fun copy(context: Context, uri: Uri): String {
        try {
            var name = "karte.pdf"
            context.contentResolver.query(uri, null, null, null, null)?.use { c ->
                if (c.moveToFirst()) {
                    val i = c.getColumnIndex(OpenableColumns.DISPLAY_NAME)
                    if (i >= 0) name = c.getString(i) ?: name
                }
            }
            val dir = File(context.cacheDir, "karte_pdf")
            dir.mkdirs()
            val out = File(dir, "${System.currentTimeMillis()}.pdf")
            val input = context.contentResolver.openInputStream(uri) ?: return "error\t\t\t読み込めませんでした"
            input.use { inp -> out.outputStream().use { o -> inp.copyTo(o) } }
            return "ok\t${out.absolutePath}\t$name\t${out.length()}"
        } catch (e: Exception) {
            return "error\t\t\t${e.message ?: "読み込めませんでした"}"
        }
    }
}
