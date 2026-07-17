# Aturan UI AppOEMS — Terkunci

Seluruh UI web AppOEMS wajib menggunakan template **Materialize** bawaan proyek ini sebagai fondasi tunggal.

- Gunakan layout Blade, komponen, asset Vite, ikon, pola menu, responsivitas, dan class yang sudah tersedia di `resources/assets` Materialize.
- Jangan menambahkan atau memigrasikan ke template/framework UI lain tanpa persetujuan tertulis Owner dan QA. Contoh yang tidak boleh diperkenalkan sendiri: Tailwind UI, MUI, Ant Design, shadcn/ui, AdminLTE, Vuexy, atau template dashboard lain.
- Branding OSM boleh ditambahkan melalui CSS/Blade yang ringan, selama tidak mengganti struktur navigasi, core CSS, atau JavaScript Materialize.
- Perubahan UI wajib tetap responsif mobile, menjaga accessibility dasar, dan lulus `scripts/verify-materialize-governance.ps1`.
- Jika Materialize belum menyediakan komponen yang dibutuhkan, pakai komponen Bootstrap/vendor yang memang sudah menjadi bagian dari bundle Materialize proyek ini; jangan memasang UI kit eksternal baru.

Aturan ini berlaku untuk fitur baru, refactor, perbaikan bug, dan integrasi berikutnya.
