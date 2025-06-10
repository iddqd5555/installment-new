<form method="POST" action="{{ route('admin.payment-info.store') }}">
    @csrf

    <div class="form-group">
        <label>เลือกธนาคาร:</label>
        <select class="form-select" id="bank-account" name="bank_account">
            <option selected disabled>เลือกธนาคาร</option>
            <option value="kbank" data-logo="/images/banks/kbank.png">กสิกรไทย</option>
            <option value="scb" data-logo="/images/banks/scb.png">ไทยพาณิชย์</option>
            <option value="truewallet" data-logo="/images/banks/truewallet.png">True Wallet</option>
        </select>
        <img id="selected-logo" src="" width="60" style="margin-top:10px;">
    </div>

    <div class="form-group mt-3">
        <label>เลขบัญชีธนาคาร:</label>
        <input type="text" class="form-control" name="account_number" placeholder="กรอกเลขบัญชี">
    </div>

    <button type="submit" class="btn btn-success mt-3">บันทึกข้อมูล</button>
</form>

<script>
document.getElementById('bank-account').addEventListener('change', function() {
    document.getElementById('selected-logo').src = this.options[this.selectedIndex].dataset.logo;
});
</script>