<script src="https://unpkg.com/html5-qrcode"></script>

<script>
    function startBarcodeScan() {
        const scanner = new Html5Qrcode("barcode-scanner");

        scanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: 250 },
            (decodedText) => {
                document.getElementById('barcode').value = decodedText;
                scanner.stop();
            }
        );
    }
</script>

<div id="barcode-scanner" style="width:300px"></div>