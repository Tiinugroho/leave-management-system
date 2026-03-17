# AI Usage Declaration

Sebagai bagian dari komitmen terhadap transparansi sesuai dengan aturan tes, berikut adalah daftar penggunaan AI dalam pengerjaan proyek ini.

**AI Model yang digunakan:** Google Gemini 3.1 Pro (Web/Chat)

## Daftar Prompt & Tooling
| Kategori | Deskripsi Penggunaan | Tools |
| :--- | :--- | :--- |
| **Slicing UI** | Mempercepat konversi mockup desain menjadi komponen Vue 3 (Sidebar, Quota Progress Bar). | Gemini |
| **Architecture** | Pembuatan komponen UI reusable (Button, Modal, DataTable, FileUpload) untuk standarisasi aplikasi. | Gemini |
| **Logic API** | Membantu penyusunan logika pengecekan saldo cuti (total_quota - used) di Controller Laravel. | Gemini |
| **Debugging** | Pemecahan error `SQLSTATE[23502]` (Not null violation) pada kolom `total_days` PostgreSQL. | Gemini |
| **Dokumentasi** | Penyusunan draf file README dan AI_USAGE agar sesuai dengan standar submission. | Gemini |

## Catatan
AI digunakan sebagai alat bantu (*pair-programmer*) untuk mempercepat penulisan kode boilerplate dan debugging. Seluruh logika bisnis inti, integrasi API, dan penyesuaian UI tetap dilakukan secara manual untuk memastikan fungsionalitas sesuai dengan spesifikasi tes.