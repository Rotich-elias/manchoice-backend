#!/bin/bash

# Check Deposit Rejection Status
# This script verifies that rejected/failed deposits are properly configured

echo "========================================="
echo "Checking Deposit Rejection Configuration"
echo "========================================="
echo ""

cd /home/smith/Desktop/MAN/manchoice-backend

echo "1. Checking deposits with 'failed' or 'rejected' status:"
php artisan tinker --execute="
echo json_encode(
  App\Models\Deposit::whereIn('status', ['failed', 'rejected'])
    ->with('loan:id,loan_number,status')
    ->get(['id', 'loan_id', 'status', 'rejection_reason', 'rejected_at', 'rejection_count'])
    ->toArray(),
  JSON_PRETTY_PRINT
);
"

echo ""
echo "2. Checking if all rejected/failed deposits have rejection reasons:"
php artisan tinker --execute="
\$withReason = App\Models\Deposit::whereIn('status', ['failed', 'rejected'])->whereNotNull('rejection_reason')->count();
\$withoutReason = App\Models\Deposit::whereIn('status', ['failed', 'rejected'])->whereNull('rejection_reason')->count();
echo 'With rejection_reason: ' . \$withReason . PHP_EOL;
echo 'Without rejection_reason: ' . \$withoutReason . PHP_EOL;
if (\$withoutReason > 0) {
  echo 'WARNING: Some rejected deposits are missing rejection reasons!' . PHP_EOL;
} else {
  echo 'SUCCESS: All rejected deposits have rejection reasons.' . PHP_EOL;
}
"

echo ""
echo "3. Sample API Response for a rejected deposit:"
LOAN_ID=$(php artisan tinker --execute="echo App\Models\Deposit::whereIn('status', ['failed', 'rejected'])->first()->loan_id ?? 0;")
if [ "$LOAN_ID" != "0" ]; then
  echo "Loan ID: $LOAN_ID"
  curl -s -X GET "http://192.168.100.65:8000/api/loans/$LOAN_ID/deposit/status" \
    -H "Authorization: Bearer 37|cYbtDE3PRsLpQxvvZJdFrOmgVLO8imYh9kx6XVrA14384142" \
    -H "Accept: application/json" | jq '.'
else
  echo "No rejected/failed deposits found for testing."
fi

echo ""
echo "========================================="
echo "Configuration Check Complete"
echo "========================================="
