<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Popup Logout</title>
    <style>
        .popup {
            display: none; /* Sembunyikan secara default */
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #1a1a1a;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            color: white;
        }

        .popup-buttons {
            margin-top: 20px;
        }

        .popup-buttons button {
            padding: 8px 16px;
            margin-right: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        #cancelBtn {
            background-color: #6c757d;
            color: white;
        }

        #cancelBtn:hover {
            background-color: #5a6268;
        }

        #logoutConfirmBtn {
            background-color: #007bff;
            color: white;
        }

        #logoutConfirmBtn:hover {
            background-color: #0056b3;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 900;
        }
    </style>
</head>
<body>
    <div id="logoutPopup" class="popup">
        <h2>Logout</h2>
        <p>Apakah Anda yakin ingin logout?</p>
        <div class="popup-buttons">
            <button id="cancelBtn" onclick="hidePopup()">Batal</button> <!-- Kembali aktif untuk menutup popup -->
            <button id="logoutConfirmBtn" onclick="confirmLogout()">Logout</button>
        </div>
    </div>

    <div id="overlay" class="overlay"></div>

    <script>
        function showPopup() {
            document.getElementById("logoutPopup").style.display = "block";
            document.getElementById("overlay").style.display = "block";
        }

        function hidePopup() {
            document.getElementById("logoutPopup").style.display = "none";
            document.getElementById("overlay").style.display = "none";
        }

        function confirmLogout() {
            window.location.href = "../logout.php?logout=true"; // Sesuaikan path ke file logout
        }
    </script>
</body>
</html>