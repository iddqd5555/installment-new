<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ยืนยันการส่งข้อมูลผ่อนทอง</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>ชื่อ-นามสกุล:</strong> <span id="modalFullName"></span></p>
                <p><strong>เลขบัตรประชาชน:</strong> <span id="modalIDCard"></span></p>
                <p><strong>เบอร์โทรศัพท์:</strong> <span id="modalPhone"></span></p>
                <p><strong>จำนวนเงินที่ผ่อน:</strong> <span id="modalGoldPrice"></span> บาท</p>
                <p><strong>ระยะเวลาผ่อน:</strong> <span id="modalPeriod"></span> วัน</p>
                <p><strong>ยอดรวมที่ต้องผ่อน:</strong> <span id="modalTotalPayment"></span> บาท</p>
                <p><strong>ยอดที่ต้องชำระต่อวัน:</strong> <span id="modalDailyPayment"></span> บาท</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" onclick="submitForm()">ยืนยันส่งข้อมูล</button>
            </div>
        </div>
    </div>
</div>
