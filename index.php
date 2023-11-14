<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php include('inc/links.php'); ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
  <title><?php echo $settings_r['site_title'] ?> - HOME</title>
  <style>
    .availability-form {
      margin-top: -50px;
      z-index: 2;
      position: relative;
    }

    @media screen and(max-width: 1575px) {
      .availability-form {
        margin-top: 25px;
        padding: 0 35px;
      }
    }
  </style>
</head>

<body class="bg-light">

  <?php require('inc/header.php'); ?>;
  <!-- Carousel -->

  <div class="container-fluid px-lg-4 mt-4">
    <div class="swiper swiper-container">
      <div class="swiper-wrapper">
        <?php
        $res = selectAll('carousel');
        while ($row = mysqli_fetch_assoc($res)) {
          $path = CAROUSEL_IMG_PATH;
          echo <<< data
            <div class="swiper-slide">
              <img src="$path$row[image]" class="w-100 d-block">
            </div>
            data;
        }
        ?>
        <div class="swiper-slide">
          <img src="images/carousel/IMG_49823.png" class="w-100 d-block">
        </div>
        <div class="swiper-slide">
          <img src="images/carousel/IMG_19095.png" class="w-100 d-block">
        </div>
        <div class="swiper-slide">
          <img src="images/carousel/IMG_20599.png" class="w-100 d-block">
        </div>
        <div class="swiper-slide">
          <img src="images/carousel/IMG_92387.png" class="w-100 d-block">
        </div>
        <div class="swiper-slide">
          <img src="images/carousel/IMG_69534.png" class="w-100 d-block">
        </div>
        <div class="swiper-slide">
          <img src="images/carousel/IMG_49823.png" class="w-100 d-block">
        </div>
      </div>
    </div>
  </div>

  <!-- check available room -->

  <div class="container availability-form">
    <div class="row">
      <div class="col-lg-12 bg-white shadow roinded p-4">
        <h5 class="mb-4">Check booking availability</h5>
        <form action="rooms.php">
          <div class="row align-items-end">
            <div class="col-lg-3 mb-3">
              <label class="form-label" style="font-weight: 500;">Check In</label>
              <input type="date" class="form-control shadow-none" name="checkin" required>
            </div>
            <div class="col-lg-3 mb-3">
              <label class="form-label" style="font-weight: 500;">Check-out</label>
              <input type="date" class="form-control shadow-none" name="checkout" required>
            </div>
            <div class="col-lg-3 mb-3">
              <label class="form-label" style="font-weight: 500;">Adult</label>
              <select class="form-select shadow-none" name="adult">
                <?php 
                  $guests_q = mysqli_query($conn,"SELECT MAX(adult) AS `max_adult`, MAX(children) AS `max_children`
                      FROM `rooms` WHERE `status` = '1' AND `removed` = '0'");
                      $guests_res = mysqli_fetch_assoc($guests_q);
                      for($i=1;$i<=$guests_res['max_adult'];$i++) {
                        echo "<option value='$i'>$i</option>";
                      }
                ?>
          
              </select>
            </div>
            <div class="col-lg-2 mb-3">
              <label class="form-label" style="font-weight: 500;">Children</label>
              <select class="form-select shadow-none" name = "children">
                <?php 
                  for($i=1;$i<$guests_res['max_children'];$i++) {
                    echo "<option value='$i'>$i</option>";
                  }
                ?>
              </select>
            </div>
            <input type="hidden" name ="check_availability">
            <div class="col-lg-1 mb-lg-3 mt-2">
              <button type="submit" class="btn text-white shadow-none custom-bg">Submit</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Our Rooms -->

  <h2 class="mt-5 pt-4 mb-4 text-center fw-bold h-font"> OUR ROOMS</h2>
  <div class="container">
    <div class="row">

      <?php
        $room_res = select("SELECT * FROM `rooms` WHERE `status`=? and `removed`=? ORDER BY `id`DESC LIMIT 3", [1, 0], 'ii');

        while ($room_data = mysqli_fetch_assoc($room_res)) {
          //get features of room

          $fea_q = mysqli_query($conn, "SELECT f.name
                  from `features` f
                  INNER JOIN `room_features` rfea on f.id = rfea.features_id
                  WHERE rfea.room_id = '$room_data[id]'");

          $features_data = "";
          while ($fea_row = mysqli_fetch_assoc($fea_q)) {
            $features_data .= "<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>
                    $fea_row[name]
                  </span>";
          }

          //get facilities of room
          $fac_q = mysqli_query($conn, "SELECT f.name
                FROM `facilities` f
                INNER JOIN `room_facilities` rfac on f.id = rfac.facilities_id
                WHERE rfac.room_id = $room_data[id]");

          $facilities_data = "";
          while ($fac_row = mysqli_fetch_assoc($fac_q)) {
            $facilities_data .= "<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>
                    $fac_row[name]
                  </span>";
          }

          //get thumnail of image
          $room_thumb = ROOMS_IMG_PATH . "thumbnail.jpg";
          $thumb_q = mysqli_query($conn, "SELECT * FROM `room_images` 
                    WHERE `room_id` = '$room_data[id]' 
                    AND `thumb` = '1'");
          if (mysqli_num_rows($thumb_q) > 0) {
            $thumb_res = mysqli_fetch_assoc($thumb_q);
            $room_thumb = ROOMS_IMG_PATH . $thumb_res['image'];
          }

          $book_btn = "";

          if(!$settings_r['shutdown']){
            $login = 0;
            if(isset($_SESSION['login']) && $_SESSION['login'] == true) {
              $login = 1;
            }

            $book_btn = "<button onclick='checkLoginToBook($login,$room_data[id])' 
            class='btn btn-sm w-100 text-white custom-bg shadow-none mb-2'>Book Now";
          }

          //print room card
          echo <<<data
                    <div class="col-lg-4 col-md-6 my-3">
                    <div class="card border-0 shadow" style="max-width: 350; margin: auto;">
                      <img src="$room_thumb" class="card-img-top">
                      <div class="card-body">
                        <h5>$room_data[name]</h5>
                        <h6 class="mb-4">$$room_data[price] per night</h6>
                        <div class="feature mb-4">
                          <h6 class="mb-1">Features</h6>
                          $features_data
                        </div>
                        <div class="facilities mb-4">
                          <h6 class="mb-1">Facilities</h6>
                          $facilities_data
                        </div>
                        <div class="guests mb-4">
                          <h6 class="mb-1">Guests</h6>
                          <span class="badge rounded-pill bg-light text-dark text-wrap">
                          $room_data[adult] Adults
                          </span>
                          <span class="badge rounded-pill bg-light text-dark text-wrap">
                          $room_data[children] Chidren
                          </span>
                        </div>
                        <div class="rating mb-4">
                          <h6 class="mb-1">Rating</h6>
                          <span class="badge rounded-pill bg-light">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                          </span>
                        </div>
                        <div class="d-flex justify-content-evenly mb-2">
                          <div >
                            $book_btn
                          </div>
                          <div>
                          
                            <a href="room_details.php?id=$room_data[id]" class="btn btn-sm btn-outline-dark shadow-none">More details</a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                data;
        }
      ?>
      <div class="col-lg-12 text-center mt-5">
        <a href="rooms.php" class="btn btn-sm btn-outline-dark rounded-0 fw-bold shadow-none">More Rooms</a>
      </div>
    </div>
  </div>

  <!-- Our Facilities -->

  <h2 class="mt-5 pt-4 mb-4 text-center fw-bold h-font"> OUR FACILITIES</h2>
  <div class="container">
    <div class="row justify-content-evenly px-lg-0 px-md-0 px-5">
      <?php
      $res = mysqli_query($conn, "SELECT * FROM `facilities` ORDER BY id DESC LIMIT 5 ");
      $path = FACILITIES_IMG_PATH;

      while ($row = mysqli_fetch_assoc($res)) {
        echo <<<data
              <div class="col-lg-2 col-md-2 text-center bg-white rounded shadow my-3 py-4">
                <img src="$path$row[icon]" width="50px">
                <h5 class="mt-3">$row[name]</h5>
              </div>
              data;
      }
      ?>
      <div class="col-lg-12 text-center mt-5">
        <a href="facilities.php" class="btn btn-sm btn-outline-dark rounded-0 fw-bold shadow-none">More Facilities</a>
      </div>
    </div>
  </div>

  <!-- Reviews -->
  <h2 class="mt-5 pt-4 mb-4 text-center fw-bold h-font"> REVIEWS</h2>

  <div class="container">
    <div class="swiper swiper-testimonials">
      <div class="swiper-wrapper mb-1">

        <div class="swiper-slide bg-white">
          <div class="profile d-flex align-items-center p-4">
            <img src="images/facilities/IMG_64366.svg" width="30px">
            <h6 class="m-0 ms-2">Smith</h6>
          </div>
          <p>
            Lorem ipsum dolor sit amet consectetur adipisicing elit.
            Rerum odit obcaecati maiores recusandae ratione excepturi minus deleniti expedita necessitatibus voluptate quia animi sed cumque,
            earum exercitationem sequi officia labore qui.
          </p>
          <div class="rating">
            <i class="bi bi-star-fill text-warning"></i>
            <i class="bi bi-star-fill text-warning"></i>
            <i class="bi bi-star-fill text-warning"></i>
            <i class="bi bi-star-fill text-warning"></i>
          </div>
        </div>
        <div class="swiper-slide bg-white">
          <div class="profile d-flex align-items-center p-4">
            <img src="images/facilities/IMG_99754.svg" width="30px">
            <h6 class="m-0 ms-2">Chee</h6>
          </div>
          <p>
            Lorem ipsum dolor sit amet consectetur adipisicing elit.
            Rerum odit obcaecati maiores recusandae ratione excepturi minus deleniti expedita necessitatibus voluptate quia animi sed cumque,
            earum exercitationem sequi officia labore qui.
          </p>
          <div class="rating">
            <i class="bi bi-star-fill text-warning"></i>
            <i class="bi bi-star-fill text-warning"></i>
            <i class="bi bi-star-fill text-warning"></i>
            <i class="bi bi-star-fill text-warning"></i>
          </div>
        </div>
        <div class="swiper-slide bg-white p-4">
          <div class="profile d-flex align-items-center p-4">
            <img src="images/facilities/IMG_82111.svg" width="30px">
            <h6 class="m-0 ms-2">Random user</h6>
          </div>
          <p>
            Lorem ipsum dolor sit amet consectetur adipisicing elit.
            Rerum odit obcaecati maiores recusandae ratione excepturi minus deleniti expedita necessitatibus voluptate quia animi sed cumque,
            earum exercitationem sequi officia labore qui.
          </p>
          <div class="rating">
            <i class="bi bi-star-fill text-warning"></i>
            <i class="bi bi-star-fill text-warning"></i>
            <i class="bi bi-star-fill text-warning"></i>
            <i class="bi bi-star-fill text-warning"></i>
          </div>
        </div>
        <div class="swiper-slide bg-white p-4">
          <div class="profile d-flex align-items-center p-4">
            <img src="images/facilities/IMG_55799.svg" width="30px">
            <h6 class="m-0 ms-2">Random user</h6>
          </div>
          <p>
            Lorem ipsum dolor sit amet consectetur adipisicing elit.
            Rerum odit obcaecati maiores recusandae ratione excepturi minus deleniti expedita necessitatibus voluptate quia animi sed cumque,
            earum exercitationem sequi officia labore qui.
          </p>
          <div class="rating">
            <i class="bi bi-star-fill text-warning"></i>
            <i class="bi bi-star-fill text-warning"></i>
            <i class="bi bi-star-fill text-warning"></i>
            <i class="bi bi-star-fill text-warning"></i>
          </div>
        </div>
      </div>
      <div class="swiper-pagination"></div>
    </div>
    <div class="col-lg-12 text-center mt-5">
      <a href="about.php" class="btn btn-sm btn-outline-dark rounded-0 fw-bold shadow-none">Know more</a>
    </div>
  </div>

  <!-- Reach us -->

  <h2 class="mt-5 pt-4 mb-4 text-center fw-bold h-font"> REACH US</h2>
  <div class="container">
    <div class="row">
      <div class="col-lg-8 col-md-8 p-4 mb-lg-0 mb-3 bg-white rounded">
        <iframe height="320px" class="w-100 rounded" src="<?php echo $contact_r['iframe'] ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
      <div class="col-lg-4 col-md-4">
        <div class="bg-white p-4 rounded mb-4">
          <h5>Call us</h5>
          <a href="tel: +<?php echo $contact_r['pn1'] ?>" class="d-inline-block mb-2 text-decoration-none text-dark">
            <i class="bi bi-telephone-fill"></i> +<?php echo $contact_r['pn1'] ?>
          </a>
          <br>
          <?php
          if ($contact_r['pn2'] != '') {
            echo <<<data
                <a href="tel: +$contact_r[pn2]" class="d-inline-block mb-2 text-decoration-none text-dark">
                <i class="bi bi-telephone-fill"></i> +$contact_r[pn2]
                </a>
              data;
          }
          ?>

        </div>
        <div class="bg-white p-4 rounded mb-4">
          <h5>Follow us</h5>
          <?php
          if ($contact_r['tw'] != '') {
            echo <<<data
                <a href="$contact_r[tw]" class="d-inline-block mb-3">
                  <span class="badge bg-light text-dark fs-6 p-2">
                    <i class="bi bi-twitter me-1"></i> Twitter
                  </span>
                </a>
                <br>
              data;
          }
          ?>

          <a href="<?php echo $contact_r['fb'] ?>" class="d-inline-block mb-3">
            <span class="badge bg-light text-dark fs-6 p-2">
              <i class="bi bi-facebook me-1"></i> Facebook
            </span>
          </a>
          <br>
          <a href="<?php echo $contact_r['insta'] ?>" class="d-inline-block mb-3">
            <span class="badge bg-light text-dark fs-6 p-2">
              <i class="bi bi-instagram me-1"></i> Instagram
            </span>
          </a>
          <br>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <?php include('inc/footer.php'); ?>

  <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
  <script>
    var swiper = new Swiper(".swiper-container", {
      spaceBetween: 30,
      effect: "fade",
      loop: true,
      autoplay: {
        delay: 2000,
        disableOnInteraction: false,
      }
    });
    var swiper = new Swiper(".swiper-testimonials", {
      effect: "coverflow",
      grabCursor: true,
      centeredSlides: true,
      slidesPerView: "auto",
      slidesPerView: "3",
      loop: true,
      coverflowEffect: {
        rotate: 50,
        stretch: 0,
        depth: 100,
        modifier: 1,
        slideShadows: false,
      },
      pagination: {
        el: ".swiper-pagination",
      },
      breakpoints: {
        320: {
          slidesPerView: 1,
        },
        640: {
          slidesPerView: 1,
        },
        768: {
          slidesPerView: 2,
        },
        1024: {
          slidesPerView: 3,
        },
      }
    });
  </script>
</body>

</html>