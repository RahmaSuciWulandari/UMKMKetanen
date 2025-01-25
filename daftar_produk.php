<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>
   Produk UMKM
  </title>
  <script src="https://cdn.tailwindcss.com">
  </script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@900&display=swap" rel="stylesheet"/>
  <style>
   .custom-font {
    font-family: 'Roboto', sans-serif;
   }
   .custom-navbar {
    background-image: linear-gradient(45deg, #ff512f 0%, #f09819 51%, #ff512f 100%);
    background-size: 200% auto;
    cursor: pointer;
    user-select: none;
    -webkit-user-select: none;
    touch-action: manipulation;
    transition: 0.5s;
   }
   .custom-navbar:hover {
    background-position: right center;
   }
   .nav-item {
    transition: color 0.3s;
   }
   .nav-item.active {
    color: #ff512f;
   }
   .nav-item:hover {
    color: #ff512f;
   }
  </style>
 </head>
 <body class="bg-gray-100">
  <div class="custom-navbar p-4 flex items-center rounded-b-lg shadow-lg">
   <div class="text-white custom-font mr-4 text-center">
   <a href="index.php" class="text-white custom-font mr-4 text-center"> 
   <h1 class="font-extrabold text-3xl italic tracking-wider">
     UMKM
    </h1>
    <h3 class="font-bold text-xl italic tracking-wider">
     Desa Ketanen
    </h3>
    </a>
   </div>
   <div class="relative flex-grow">
    <input class="w-full p-3 rounded-full pl-12 text-gray-700" placeholder="Cari" type="text"/>
    <i class="fas fa-search absolute left-4 top-4 text-gray-500">
    </i>
   </div>
  </div>
  <div class="p-2 grid grid-cols-2 gap-2">
   <div class="bg-white p-1 rounded-md">
    <img alt="Transparent washbag pouch" class="w-full rounded-md" height="150" src="https://storage.googleapis.com/a1aa/image/esqHVBNY3C0MGy9dy6sZFycZOt6GhE89b5JcPbayf02yPgGUA.jpg" width="225"/>
    <p class="text-xs mt-1">
     HO Tas Kosmetik Transparant WASHBAG Pouch
    </p>
    <div class="flex justify-between items-center mt-1">
     <span class="text-red-500 font-bold">
      Rp14.081
     </span>
     <span class="text-gray-500 text-xs">
      10RB+ Dilihat
     </span>
    </div>
   </div>
   <div class="bg-white p-1 rounded-md">
    <img alt="Pack of tissues" class="w-full rounded-md" height="150" src="https://storage.googleapis.com/a1aa/image/CaSqTGACwqJKANKLKoTMXzaxmTqohsbEKsehDIPXwig4HQDKA.jpg" width="225"/>
    <p class="text-xs mt-1">
     Rp9999 4 Pack FLASH SALE! GISSE Tisu Wajah 360.
    </p>
    <div class="flex justify-between items-center mt-1">
     <span class="text-red-500 font-bold">
      Rp18.500
     </span>
     <span class="text-gray-500 text-xs">
      10RB+ Dilihat
     </span>
    </div>
   </div>

   <div class="bg-white p-1 rounded-md">
    <img alt="Transparent washbag pouch" class="w-full rounded-md" height="150" src="https://storage.googleapis.com/a1aa/image/esqHVBNY3C0MGy9dy6sZFycZOt6GhE89b5JcPbayf02yPgGUA.jpg" width="225"/>
    <p class="text-xs mt-1">
     HO Tas Kosmetik Transparant WASHBAG Pouch
    </p>
    <div class="flex justify-between items-center mt-1">
     <span class="text-red-500 font-bold">
      Rp14.081
     </span>
     <span class="text-gray-500 text-xs">
      10RB+ Dilihat
     </span>
    </div>
   </div>
   <div class="bg-white p-1 rounded-md">
    <img alt="Pack of tissues" class="w-full rounded-md" height="150" src="https://storage.googleapis.com/a1aa/image/CaSqTGACwqJKANKLKoTMXzaxmTqohsbEKsehDIPXwig4HQDKA.jpg" width="225"/>
    <p class="text-xs mt-1">
     Rp9999 4 Pack FLASH SALE! GISSE Tisu Wajah 360.
    </p>
    <div class="flex justify-between items-center mt-1">
     <span class="text-red-500 font-bold">
      Rp18.500
     </span>
     <span class="text-gray-500 text-xs">
      10RB+ Dilihat
     </span>
    </div>
   </div>

  </div>
  <!-- <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 flex justify-around items-center p-2">
   <div class="flex flex-col items-center nav-item ">
   <a href="index.php" class="flex flex-col items-center nav-item "> 
   <i class="fas fa-home">
    </i>
    <span class="text-xs">
     Beranda
    </span>
    </a>
   </div>
   <div class="flex flex-col items-center nav-item">
   <a href="daftar_produk.php" class="flex flex-col items-center nav-item">
    <i class="fas fa-fire">
    </i>
    <span class="text-xs">
     Produk
    </span>
    </a>
   </div>
   <div class="flex flex-col items-center nav-item">
    <i class="fas fa-video">
    </i>
    <span class="text-xs">
     Live &amp; Video
    </span>
   </div>
   <div class="flex flex-col items-center nav-item">
    <i class="fas fa-bell">
    </i>
    <span class="text-xs">
     Notifikasi
    </span>
   </div>
   <div class="flex flex-col items-center nav-item">
    <i class="fas fa-user">
    </i>
    <span class="text-xs">
     Saya
    </span>
   </div>
  </div> 
  <script>
   document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', () => {
     document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
     item.classList.add('active');
    });
   });
  </script>-->
 </body>
</html>