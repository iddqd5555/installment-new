<div class="card text-center">
    <div class="card-body">
        <img src="/images/banks/{{ $paymentInfo->bank_account }}.png" width="80">
        <h4>{{ ucfirst($paymentInfo->bank_account) }}</h4>
        <p>เลขบัญชี: {{ $paymentInfo->account_number }}</p>
    </div>
</div>