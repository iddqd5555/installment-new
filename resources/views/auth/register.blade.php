<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
        @csrf

        <input type="text" name="name" required placeholder="ชื่อจริง">
        <input type="text" name="surname" required placeholder="นามสกุล">
        <input type="text" name="phone" required placeholder="เบอร์โทรศัพท์">
        <input type="password" name="password" required placeholder="รหัสผ่าน">
        <input type="text" name="id_card_number" required placeholder="เลขบัตรประชาชน">

        <label>สำเนาบัตรประชาชน*</label>
        <input type="file" name="id_card_image" required>

        <label>สำเนาทะเบียนบ้าน</label>
        <input type="file" name="house_registration_image">

        <label>สำเนาทะเบียนการค้า</label>
        <input type="file" name="business_registration_image">

        <label>สำเนา Statement</label>
        <input type="file" name="bank_statement_image">

        <label>สำเนาบัญชีธนาคาร</label>
        <input type="file" name="bank_account_image">

        <label>หมายเลขพนักงานแนะนำ</label>
        <input type="text" name="staff_reference">

        <button type="submit">สมัครสมาชิก</button>
    </form>

</x-guest-layout>
