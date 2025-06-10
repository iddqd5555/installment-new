<div class="payment-details my-4 text-center">
    <img id="bank-logo" src="" alt="โลโก้ธนาคาร" width="80">
    <h4 id="bank-name"></h4>
    <p class="alert alert-warning">
        ⚠️ ยอดที่ต้องชำระ: <strong class="text-danger" id="pay-amount">1,500 บาท</strong>
    </p>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bankName = 'ธนาคารกสิกรไทย';
    const bankLogoPath = '/images/banks/kbank.png';
    const payAmount = '1,500 บาท';

    document.getElementById('bank-logo').src = bankLogoPath;
    document.getElementById('bank-name').textContent = bankName;
    document.getElementById('pay-amount').textContent = payAmount;
});
</script>