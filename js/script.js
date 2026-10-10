$(document).ready(function () {

    /* =================================
       MOBILE MENU
    ================================= */

    $("#menuButton").click(function () {

        $("#mobileMenu")
            .stop(true, true)
            .slideToggle(250);

    });

    $("#mobileMenu a").click(function () {

        $("#mobileMenu")
            .stop(true, true)
            .slideUp(200);

    });


    /* =================================
       NAVBAR
    ================================= */

    function checkNavbar() {

        if ($(window).scrollTop() > 30) {

            $("nav").addClass("shadow-lg");

        } else {

            $("nav").removeClass("shadow-lg");

        }

    }

    checkNavbar();

    $(window).on("scroll", checkNavbar);


    /* =================================
       HERO MENU
    ================================= */

    if ($(".menu-hero-content").length) {

        setTimeout(function () {

            $(".menu-hero-content").addClass("show");

        }, 150);

    }


    /* =================================
       HERO INDEX
    ================================= */

    if ($("#heroText").length) {

        setTimeout(function () {

            $("#heroText").addClass("show");

            $(".hero-image").addClass("show");

        }, 150);

    }


    /* =================================
       FEATURE CARD
    ================================= */

    function showFeatureCards() {

        $(".feature-card").each(function (index) {

            let cardTop =
                $(this).offset().top;

            let screenBottom =
                $(window).scrollTop() +
                $(window).height();

            if (screenBottom > cardTop + 80) {

                let card = $(this);

                setTimeout(function () {

                    card.addClass("show");

                }, index * 150);

            }

        });

    }

    if ($(".feature-card").length) {

        showFeatureCards();

        $(window).on(
            "scroll",
            showFeatureCards
        );

    }


    /* =================================
       TOMBOL DAFTAR MENU
    ================================= */

    $(".menu-scroll-button").click(function (event) {

        event.preventDefault();

        let target =
            $(this).attr("href");

        if ($(target).length) {

            $("html, body").animate({

                scrollTop:
                    $(target).offset().top - 70

            }, 700);

        }

    });


    /* =================================
       ANIMASI SELURUH MENU
    ================================= */

    if (
        $(".menu-card").length ||
        $(".extra-card").length ||
        $(".drink-card").length
    ) {

        $("body").addClass(
            "menu-animation"
        );


        /* =================================
           JUDUL MENU
        ================================= */

        let menuList =
            $("#menuList");

        if (menuList.length) {

            menuList.children().first()
                .addClass(
                    "menu-title-animation"
                );

            setTimeout(function () {

                menuList.children().first()
                    .addClass("show");

            }, 150);

        }


        /* =================================
           JUDUL SECTION
        ================================= */

        $("#menuList h2, #menuList h3")
            .each(function () {

                if (
                    !$(this)
                        .closest(".menu-card")
                        .length &&

                    !$(this)
                        .closest(".extra-card")
                        .length &&

                    !$(this)
                        .closest(".drink-card")
                        .length
                ) {

                    $(this).addClass(
                        "menu-title-animation"
                    );

                }

            });


        const titleObserver =
            new IntersectionObserver(

                function (entries) {

                    entries.forEach(
                        function (entry) {

                            if (
                                entry.isIntersecting
                            ) {

                                $(entry.target)
                                    .addClass("show");

                            }

                        }
                    );

                },

                {
                    threshold: 0.2
                }

            );


        $(".menu-title-animation")
            .each(function () {

                titleObserver.observe(this);

            });


        /* =================================
           CARD MENU
        ================================= */

        const menuObserver =
            new IntersectionObserver(

                function (entries) {

                    entries.forEach(
                        function (entry) {

                            if (
                                entry.isIntersecting
                            ) {

                                let card =
                                    $(entry.target);

                                let index =
                                    card
                                        .parent()
                                        .children()
                                        .index(card);

                                let delay =
                                    (index % 3) * 120;

                                setTimeout(
                                    function () {

                                        card.addClass(
                                            "reveal"
                                        );

                                    },
                                    delay
                                );

                            }

                        }
                    );

                },

                {
                    threshold: 0.15,

                    rootMargin:
                        "0px 0px -50px 0px"
                }

            );


        $(
            ".menu-card, .extra-card, .drink-card"
        ).each(function () {

            menuObserver.observe(this);

        });

    }


    /* =================================
       HERO INFORMASI / KONTAK
    ================================= */

    if ($(".page-hero-content").length) {

        setTimeout(function () {

            $(".page-hero-content")
                .addClass("show");

        }, 150);

    }


    /* =================================
       DOKUMENTASI
    ================================= */

    if ($(".documentation-card").length) {

        const documentationObserver =
            new IntersectionObserver(

                function (entries) {

                    entries.forEach(
                        function (entry) {

                            if (
                                entry.isIntersecting
                            ) {

                                $(entry.target)
                                    .addClass("show");

                            }

                        }
                    );

                },

                {
                    threshold: 0.15
                }

            );


        $(".documentation-card")
            .each(function () {

                documentationObserver
                    .observe(this);

            });

    }


    /* =================================
       KONTAK
    ================================= */

    if (
        $(".contact-card, .contact-form")
            .length
    ) {

        const contactObserver =
            new IntersectionObserver(

                function (entries) {

                    entries.forEach(
                        function (entry) {

                            if (
                                entry.isIntersecting
                            ) {

                                $(entry.target)
                                    .addClass("show");

                            }

                        }
                    );

                },

                {
                    threshold: 0.15
                }

            );


        $(
            ".contact-card, .contact-form"
        ).each(function () {

            contactObserver.observe(this);

        });

    }


    /* =================================
       RESPONSIVE
    ================================= */

    $(window).resize(function () {

        if ($(window).width() >= 768) {

            $("#mobileMenu").hide();

        }

    });

});



/* =================================
   PESANAN
================================= */

$(document).ready(function () {

    let keranjang = [];


    /* =================================
       TAMBAHKAN TOMBOL PESAN
    ================================= */

    $(".menu-card").each(function () {

        let card = $(this);

        let nama =
            card.find("h3")
                .text()
                .trim();

        let harga =
            card.find("p")
                .first()
                .text()
                .trim();


        /*
        Jangan menambahkan tombol
        jika sudah ada.
        */

        if (
            nama &&
            harga &&
            card.find(".order-button").length === 0
        ) {

            card.find(".p-6").append(`

                <button
                    type="button"
                    class="order-button mt-4 w-full
                           bg-orange-500 text-black
                           py-3 rounded-xl
                           font-black
                           hover:bg-orange-400
                           hover:-translate-y-1
                           transition"
                    data-name="${nama}"
                    data-price="${harga}">

                    🛒 Pesan

                </button>

            `);

        }

    });


    /* =================================
       TAMBAHAN
    ================================= */

    $("#menuList section").each(
        function () {

            $(this)
                .find(".grid > div")
                .each(function () {

                    let card = $(this);

                    let nama =
                        card.find("h3")
                            .text()
                            .trim();

                    let harga =
                        card.find("p")
                            .first()
                            .text()
                            .trim();


                    if (
                        nama &&
                        harga &&
                        card.find(
                            ".order-button"
                        ).length === 0
                    ) {

                        card.append(`

                            <button
                                type="button"
                                class="order-button mt-4 w-full
                                       bg-orange-500 text-black
                                       py-2 rounded-lg
                                       font-black
                                       hover:bg-orange-400
                                       transition"
                                data-name="${nama}"
                                data-price="${harga}">

                                🛒 Pesan

                            </button>

                        `);

                    }

                });

        }
    );


    /* =================================
       MINUMAN
    ================================= */

    $(".grid.lg\\:grid-cols-2 .flex")
        .each(function () {

            let item = $(this);

            let nama =
                item.find("span")
                    .first()
                    .text()
                    .trim();

            let harga =
                item.find("strong")
                    .first()
                    .text()
                    .trim();


            if (
                nama &&
                harga &&
                item.find(
                    ".order-button"
                ).length === 0
            ) {

                item.append(`

                    <button
                        type="button"
                        class="order-button ml-3
                               bg-orange-500 text-black
                               px-3 py-1 rounded-lg
                               font-bold text-sm
                               hover:bg-orange-400
                               transition"
                        data-name="${nama}"
                        data-price="${harga}">

                        🛒

                    </button>

                `);

            }

        });


    /* =================================
       KLIK PESAN
    ================================= */

    $(document).on(
        "click",
        ".order-button",
        function () {

            let nama =
                $(this).data("name");

            let harga =
                $(this).data("price");


            let jumlah =
                prompt(
                    "Mau pesan berapa " +
                    nama +
                    "?",
                    "1"
                );


            if (
                !jumlah ||
                isNaN(jumlah) ||
                jumlah <= 0
            ) {

                return;

            }


            keranjang.push({

                nama: nama,

                harga: harga,

                jumlah:
                    parseInt(jumlah)

            });


            updateKeranjang();

        }
    );


    /* =================================
       TAMPILKAN KERANJANG
    ================================= */

    function updateKeranjang() {

        let daftar = "";


        keranjang.forEach(
            function (item, index) {

                daftar += `

                    <div
                        class="flex justify-between
                               items-center
                               border-b border-zinc-800
                               py-3">

                        <div>

                            <p class="font-bold">
                                ${item.nama}
                            </p>

                            <p class="text-orange-500">
                                ${item.jumlah}
                                ×
                                ${item.harga}
                            </p>

                        </div>


                        <button
                            type="button"
                            onclick="hapusPesanan(${index})"
                            class="text-red-500
                                   font-bold
                                   hover:text-red-400">

                            ✕

                        </button>

                    </div>

                `;

            }
        );


        $("#orderList")
            .html(daftar);


        $("#orderModal")
            .removeClass("hidden");

    }


    /* =================================
       HAPUS PESANAN
    ================================= */

    window.hapusPesanan =
        function (index) {

            keranjang.splice(
                index,
                1
            );

            updateKeranjang();

        };


    /* =================================
       TUTUP MODAL
    ================================= */

    $(document).on(
        "click",
        "#closeOrder",
        function () {

            $("#orderModal")
                .addClass("hidden");

        }
    );


    /* =================================
       KIRIM PESANAN KE API PHP
    ================================= */

    $(document).on(
        "click",
        "#sendWhatsApp",
        function () {

            /*
            =============================
            CEK KERANJANG
            =============================
            */

            if (
                keranjang.length === 0
            ) {

                alert(
                    "Belum ada menu yang dipilih."
                );

                return;

            }


            /*
            =============================
            AMBIL NAMA
            =============================
            */

            let namaPelanggan =
                $("#customerName")
                    .val()
                    .trim();


            /*
            =============================
            AMBIL CATATAN
            =============================
            */

            let catatan =
                $("#customerNote")
                    .val()
                    .trim();


            /*
            =============================
            VALIDASI NAMA
            =============================
            */

            if (!namaPelanggan) {

                alert(
                    "Silakan masukkan nama terlebih dahulu."
                );

                $("#customerName")
                    .focus();

                return;

            }


            /*
            =============================
            UBAH KERANJANG KE JSON
            =============================
            */

            let dataPesanan =
                JSON.stringify(
                    keranjang
                );


            /*
            =============================
            NONAKTIFKAN TOMBOL
            =============================
            */

            let tombol =
                $("#sendWhatsApp");

            tombol
                .prop("disabled", true)
                .text(
                    "⏳ Menyimpan Pesanan..."
                );


            /*
            =============================
            KIRIM KE API.PHP
            =============================
            */

            $.ajax({

                url:
                    "api.php?action=order",

                type:
                    "POST",

                data: {

                    nama:
                        namaPelanggan,

                    pesanan:
                        dataPesanan,

                    catatan:
                        catatan

                },

                dataType:
                    "json",


                /*
                =========================
                BERHASIL
                =========================
                */

                success:
                    function (response) {

                        if (
                            response.status ===
                            "success"
                        ) {

                            /*
                            =================
                            BUKA WHATSAPP
                            =================
                            */

                            window.open(
                                response
                                    .data
                                    .whatsapp,
                                "_blank"
                            );


                        } else {

                            alert(
                                response.message
                            );

                        }

                    },


                /*
                =========================
                ERROR
                =========================
                */

                error:
                    function (
                        xhr,
                        status,
                        error
                    ) {

                        console.log(
                            "API Error:",
                            xhr.responseText
                        );


                        alert(
                            "Terjadi kesalahan saat menyimpan pesanan."
                        );

                    },


                /*
                =========================
                SELESAI
                =========================
                */

                complete:
                    function () {

                        tombol
                            .prop(
                                "disabled",
                                false
                            )
                            .html(
                                "📱 Pesan via WhatsApp"
                            );

                    }

            });

        }

    );



    /* =================================
       FORM KONTAK
    ================================= */

    $(document).on(
        "submit",
        "#contactForm",
        function (e) {

            e.preventDefault();


            let nama =
                $("#nama")
                    .val()
                    .trim();


            let pesan =
                $("#pesan")
                    .val()
                    .trim();


            /*
            =============================
            VALIDASI
            =============================
            */

            if (!nama) {

                alert(
                    "Nama wajib diisi."
                );

                $("#nama").focus();

                return;

            }


            if (!pesan) {

                alert(
                    "Pesan wajib diisi."
                );

                $("#pesan").focus();

                return;

            }


            /*
            =============================
            KIRIM KE API
            =============================
            */

            $.ajax({

                url:
                    "api.php?action=message",

                type:
                    "POST",

                data: {

                    nama:
                        nama,

                    pesan:
                        pesan

                },

                dataType:
                    "json",


                success:
                    function (response) {

                        if (
                            response.status ===
                            "success"
                        ) {

                            alert(
                                "Pesan berhasil dikirim."
                            );


                            $("#contactForm")[0]
                                .reset();


                        } else {

                            alert(
                                response.message
                            );

                        }

                    },


                error:
                    function () {

                        alert(
                            "Terjadi kesalahan saat mengirim pesan."
                        );

                    }

            });

        }

    );

});