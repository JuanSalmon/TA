<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">

<head>
  <!-- Required meta tags -->
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Favicon icon-->
  <link rel="shortcut icon" type="image/png" href="../src/assets/images/logos/logo-pnk.png"/>

  <!-- Core Css -->
  <link rel="stylesheet" href="../src/assets/css/styles.css" />

  <title>Broker MQTT PNK</title>
  <!-- Owl Carousel  -->
  <link rel="stylesheet" href="../src/assets/libs/owl.carousel/dist/assets/owl.carousel.min.css" />
  <link rel="stylesheet" href="../src/assets/libs/aos/dist/aos.css" />

  <style>
  .preloader img {
    transform: scale(5); /* Memperbesar gambar 5x lipat */
  }
  .logo-container {
    margin-left: -50px; /* Geser logo lebih ke kiri */
  }
  .broker-text {
    font-size: 20px;
    font-weight: bold;
    color: #333; /* Warna teks */
    white-space: nowrap;
  }
  .image-wrapper {
    width: 120%; /* Lebar wrapper (sesuaikan ukuran potongan) */
    overflow: hidden; /* Sembunyikan bagian yang melebihi wrapper */
    margin-left: auto; /* Pindahkan wrapper ke kanan */
    display: block;
  }

  .image-wrapper img {
    transform: translateX(-20%); /* Geser gambar ke kiri sehingga bagian kirinya terpotong */
  }

  .slideup {
    animation: slideUp 3s ease-in-out ;
  }

  @keyframes slideUp {
    from {
      transform: translateY(20px);
      opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
    }
}
  </style>


</head>

<body>
  <div class="toast toast-onload align-items-center text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-body hstack align-items-start gap-6">
      <i class="ti ti-alert-circle fs-6"></i>
      <div>
        <h5 class="text-white fs-3 mb-1">Welcome to Broker MQTT</h5>
      </div>
      <button type="button" class="btn-close btn-close-white fs-2 m-0 ms-auto shadow-none" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
  <!-- Preloader -->
  <div class="preloader">
    <img src="../src/assets/images/logos/logo-pnk.png" alt="loader" class="lds-ripple img-fluid" />
  </div>
  <div id="main-wrapper flex-column">
    <header class="header">
      <nav class="navbar navbar-expand-lg py-0">
        <div class="container">
          <a class="navbar-brand d-flex align-items-center logo-container" href="index.php">
            <img src="../src/assets/images/logos/logo-pnk.png"  class="dark-logo me-3" alt="Logo-Dark img-fluid" style="width: 80px; height: auto;" />
            <span class="broker-text">Broker PNK</span>
          </a>
          <button class="navbar-toggler d-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="ti ti-menu-2 fs-9"></i>
          </button>
          <button class="navbar-toggler border-0 p-0 shadow-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
            <i class="ti ti-menu-2 fs-9"></i>
          </button>
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav align-items-center mb-2 mb-lg-0 ms-auto">
              <li class="nav-item ms-2">
                <a class="btn btn-primary fs-3 rounded btn-hover-shadow px-3 py-2" href="../login/index.php">Login</a>
              </li>
              <li class="nav-item ms-2">
                <a class="btn btn-outline-primary fs-3 rounded btn-hover-shadow px-3 py-2" href="../regis/index.php">Register</a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </header>
    <div class="body-wrapper overflow-hidden pt-0">
      <section class="hero-section position-relative overflow-hidden mb-0 mb-lg-5">
        <div class="container">
          <div class="row align-items-center">
            <div class="col-xl-6">
              <div class="hero-content my-5 my-xl-0">
                <h1 class="fw-bolder mb-7 fs-13" data-aos="fade-up" data-aos-delay="400" data-aos-duration="1000">
                  Broker MQTT
                  <span class="text-primary">Politeknik Negeri Kupang</span>
                </h1>
                <div class="d-sm-flex align-items-center gap-3" data-aos="fade-up" data-aos-delay="800" data-aos-duration="1000">
                  <a class="btn btn-primary px-5 py-6 btn-hover-shadow d-block mb-3 mb-sm-0" href="../login/index.php">Login</a>
                  <h5>or</h5>
                  <a class="btn btn-outline-primary d-block scroll-link px-7 py-6" href="../regis/index.php">Register</a>
                </div>
              </div>
            </div>
            <!-- scroll image  -->
            <div class="col-xl-6 d-none d-xl-block">
              <div class="hero-img-slide position-relative bg-primary-subtle p-4 rounded">
                <div class="clearfix">
                  <div class="float-right">
                    <div class="banner-img-1 slideup image-wrapper">
                      <img src="../src/assets/images/frontend-pages/pnk2.png" alt="modernize-img" class="img-fluid " />
                    </div>
                    <div class="banner-img-2 slideup image-wrapper mt-3">
                      <img src="../src/assets/images/frontend-pages/pnk3.png" alt="modernize-img" class="img-fluid" />
                    </div>
                  </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="production pb-5 pb-md-5 mb-5" id="production-template">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
              <h2 class="text-center mb-0 fs-9 fw-bolder">
              Lorem ipsum dolor sit amet, consectetur adipisicing elit. Explicabo cupiditate numquam inventore ullam rerum, quia veritatis similique tenetur id labore. Earum laborum provident, nihil error assumenda repellendus molestias expedita unde!
              </h2>
            </div>
          </div>
          
        </div>
      </section>
      <section class="bg-primary pt-5 pb-8">
        <div class="container">
          <div class="row justify-content-between">
            <div class="col-lg-7 col-xl-5 pt-lg-5 mb-5 mb-lg-0">
              <h2 class="fs-9 text-white text-center text-lg-start fw-bolder mb-7">
                Segera hubungkan Perangkat IoT Anda Dengan Broker PNK
              </h2>
              <div class="d-sm-flex align-items-center justify-content-center justify-content-lg-start gap-3">
                <a href="../login/index.php" class="btn bg-white text-primary fw-semibold d-block mb-3 mb-sm-0 btn-hover-shadow px-7 py-6">Login</a>
                <a href="../regis/index.php" class="btn border-white text-white fw-semibold btn-hover-white d-block px-7 py-6">Register</a>
              </div>
            </div>
            <div class="col-lg-5 col-xl-5">
              <div class="text-center text-lg-end">
                <img src="../src/assets/images/backgrounds/business-woman-checking-her-mail.png" alt="modernize-img" class="img-fluid" />
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
    <!-- footer -->
    <footer class="footer-part pt-7 pb-5">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-4">
            <div class="text-center">
              <a href="index-new.html">
                <img src="../src/assets/images/logos/logo-pnk.png" alt="modernize-img" class="img-fluid pb-3" style="width: 35px; height: auto;"/>
              </a>
              <p class="mb-0 text-dark">
                By PNK 2025
              </p>
            </div>
          </div>
        </div>
      </div>
    </footer>
        
  <div class="dark-transparent sidebartoggler"></div>
  <script src="../src/assets/js/vendor.min.js"></script>
  <!-- Import Js Files -->
  <script src="../src/assets/libs/simplebar/dist/simplebar.min.js"></script>
  <script src="../src/assets/js/theme/app.init.js"></script>
  <script src="../src/assets/js/theme/theme.js"></script>
  <script src="../src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../src/assets/js/theme/app.min.js"></script>

  <!-- solar icons -->
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
  <script src="../src/assets/libs/owl.carousel/dist/owl.carousel.min.js"></script>
  <script src="../src/assets/libs/aos/dist/aos.js"></script>
  <script src="../src/assets/js/landingpage/landingpage.js"></script>
</body>

</html>