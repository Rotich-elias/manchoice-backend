# Popup Response Guide for Frontend

## Overview
When a user tries to apply for a loan or pay a deposit before their credit limit is set, the API now returns a structured response designed to trigger a **big, friendly popup** instead of an error toast.

---

## Response Format

### Structure
```json
{
  "success": false,
  "show_popup": true,
  "popup_type": "info|warning|error",
  "popup_title": "Title Here",
  "popup_icon": "⏳|💰|❌",
  "message": "Short message for toast/fallback",
  "popup_message": "Long detailed message with line breaks",
  "credit_limit_not_set": true,
  "status": "awaiting_admin_review",
  "action_required": "wait_for_review|pay_registration_fee|wait_for_admin_approval",
  "estimated_wait": "Usually within 24-48 hours",
  "action_button_text": "Optional button text",
  "registration_fee_amount": 300.00
}
```

### Key Fields

| Field | Type | Description |
|-------|------|-------------|
| `show_popup` | boolean | If `true`, show a popup dialog instead of toast |
| `popup_type` | string | Type of popup: `info`, `warning`, `error` |
| `popup_title` | string | Title for the popup header |
| `popup_icon` | string | Emoji icon to display |
| `popup_message` | string | Main message (can contain `\n` for line breaks) |
| `action_required` | string | What the user needs to do next |
| `estimated_wait` | string | How long to wait (if applicable) |
| `action_button_text` | string | Text for action button (optional) |

---

## Scenario 1: Second Loan Application (Fee Paid)

### When It Triggers
- User already has a loan application
- Registration fee has been paid
- Credit limit = 0 (admin hasn't set it yet)
- User tries to apply for another loan

### API Response
```json
{
  "success": false,
  "show_popup": true,
  "popup_type": "info",
  "popup_title": "Application Under Review",
  "popup_icon": "⏳",
  "message": "Your loan application is currently under review by our admin team.",
  "popup_message": "Thank you for your patience!\n\nYour first loan application has been submitted and is currently being reviewed by our admin team.\n\nOnce the review is complete, we will set your loan limit and notify you. After that, you'll be able to apply for loans.\n\nPlease check back later or wait for our notification.",
  "credit_limit_not_set": true,
  "registration_fee_paid": true,
  "status": "awaiting_admin_review",
  "action_required": "wait_for_review",
  "estimated_wait": "Usually within 24-48 hours"
}
```

### HTTP Status Code
`202 Accepted` - Request acknowledged but cannot be processed yet

### Frontend Implementation

#### React/React Native Example
```jsx
import { Alert } from 'react-native';

const applyForLoan = async (loanData) => {
  try {
    const response = await api.post('/loans', loanData);

    if (response.data.success) {
      // Handle success
      showSuccessToast(response.data.message);
    }
  } catch (error) {
    const data = error.response?.data;

    // Check if we should show a popup
    if (data?.show_popup) {
      showBigPopup(data);
    } else {
      // Regular error toast
      showErrorToast(data?.message || 'An error occurred');
    }
  }
};

const showBigPopup = (data) => {
  Alert.alert(
    `${data.popup_icon} ${data.popup_title}`,
    data.popup_message,
    [
      {
        text: 'OK',
        style: 'default',
      },
    ],
    { cancelable: false }
  );
};
```

#### Flutter Example
```dart
Future<void> applyForLoan(Map<String, dynamic> loanData) async {
  try {
    final response = await api.post('/loans', data: loanData);

    if (response.data['success']) {
      showSuccessToast(response.data['message']);
    }
  } catch (e) {
    if (e is DioError) {
      final data = e.response?.data;

      if (data?['show_popup'] == true) {
        _showBigPopup(
          title: '${data['popup_icon']} ${data['popup_title']}',
          message: data['popup_message'],
          type: data['popup_type'],
        );
      } else {
        showErrorToast(data?['message'] ?? 'An error occurred');
      }
    }
  }
}

void _showBigPopup({
  required String title,
  required String message,
  required String type,
}) {
  showDialog(
    context: context,
    barrierDismissible: false,
    builder: (BuildContext context) {
      return AlertDialog(
        title: Text(title),
        content: Text(message),
        actions: [
          TextButton(
            child: Text('OK'),
            onPressed: () {
              Navigator.of(context).pop();
            },
          ),
        ],
      );
    },
  );
}
```

---

## Scenario 2: Second Loan Application (Fee NOT Paid)

### When It Triggers
- User already has a loan application
- Registration fee has NOT been paid
- Credit limit = 0
- User tries to apply for another loan

### API Response
```json
{
  "success": false,
  "show_popup": true,
  "popup_type": "warning",
  "popup_title": "Registration Fee Required",
  "popup_icon": "💰",
  "message": "Please complete your registration payment to continue.",
  "popup_message": "Almost there!\n\nYou have submitted a loan application, but you need to pay the KES 300 registration fee first.\n\nOnce the registration fee is paid, our admin team will review your application and set your loan limit.\n\nPlease complete the payment to proceed.",
  "credit_limit_not_set": true,
  "registration_fee_paid": false,
  "registration_fee_amount": 300.00,
  "action_required": "pay_registration_fee",
  "action_button_text": "Pay KES 300 Now"
}
```

### HTTP Status Code
`402 Payment Required`

### Frontend Implementation

#### React/React Native Example
```jsx
const showBigPopup = (data) => {
  const buttons = [
    {
      text: 'Cancel',
      style: 'cancel',
    },
  ];

  // Add action button if specified
  if (data.action_button_text && data.action_required === 'pay_registration_fee') {
    buttons.unshift({
      text: data.action_button_text,
      onPress: () => {
        // Navigate to payment screen
        navigation.navigate('RegistrationFeePayment', {
          amount: data.registration_fee_amount,
        });
      },
    });
  }

  Alert.alert(
    `${data.popup_icon} ${data.popup_title}`,
    data.popup_message,
    buttons,
    { cancelable: false }
  );
};
```

#### Flutter Example
```dart
void _showPaymentRequiredPopup(Map<String, dynamic> data) {
  showDialog(
    context: context,
    barrierDismissible: false,
    builder: (BuildContext context) {
      return AlertDialog(
        title: Text('${data['popup_icon']} ${data['popup_title']}'),
        content: Text(data['popup_message']),
        actions: [
          TextButton(
            child: Text('Cancel'),
            onPressed: () {
              Navigator.of(context).pop();
            },
          ),
          if (data['action_button_text'] != null)
            ElevatedButton(
              child: Text(data['action_button_text']),
              onPressed: () {
                Navigator.of(context).pop();
                // Navigate to payment screen
                Navigator.pushNamed(
                  context,
                  '/registration-fee-payment',
                  arguments: {
                    'amount': data['registration_fee_amount'],
                  },
                );
              },
            ),
        ],
      );
    },
  );
}
```

---

## Scenario 3: Deposit Payment (Before Approval)

### When It Triggers
- User tries to pay deposit
- Credit limit = 0 (loan not approved yet)
- User tries to initiate M-PESA payment for deposit

### API Response
```json
{
  "success": false,
  "show_popup": true,
  "popup_type": "info",
  "popup_title": "Loan Under Review",
  "popup_icon": "⏳",
  "message": "Your loan application is currently under review.",
  "popup_message": "Please wait for admin review!\n\nYour loan application is currently being reviewed by our admin team. Once approved and your loan limit is set, you'll be able to proceed with the deposit payment.\n\nYou will be notified once the review is complete.\n\nThank you for your patience!",
  "credit_limit_not_set": true,
  "status": "awaiting_admin_review",
  "action_required": "wait_for_admin_approval",
  "estimated_wait": "Usually within 24-48 hours"
}
```

### HTTP Status Code
`202 Accepted`

### Frontend Implementation

#### React/React Native Example
```jsx
const initiateDepositPayment = async (loanId, phoneNumber) => {
  try {
    const response = await api.post(`/loans/${loanId}/deposit/mpesa`, {
      phone_number: phoneNumber,
    });

    if (response.data.success) {
      showSuccessToast('Payment initiated. Check your phone.');
    }
  } catch (error) {
    const data = error.response?.data;

    if (data?.show_popup) {
      // Show informational popup
      Alert.alert(
        `${data.popup_icon} ${data.popup_title}`,
        data.popup_message + (data.estimated_wait ? `\n\n${data.estimated_wait}` : ''),
        [{ text: 'OK' }],
        { cancelable: false }
      );
    } else {
      showErrorToast(data?.message || 'Payment failed');
    }
  }
};
```

---

## UI Design Guidelines

### Popup Styling by Type

#### Info (`popup_type: "info"`)
```
┌─────────────────────────────────────┐
│  ⏳  Application Under Review        │
├─────────────────────────────────────┤
│                                     │
│  Thank you for your patience!       │
│                                     │
│  Your first loan application has    │
│  been submitted and is currently    │
│  being reviewed by our admin team.  │
│                                     │
│  Once the review is complete, we    │
│  will set your loan limit and       │
│  notify you. After that, you'll be  │
│  able to apply for loans.           │
│                                     │
│  Please check back later or wait    │
│  for our notification.              │
│                                     │
│  Usually within 24-48 hours         │
│                                     │
├─────────────────────────────────────┤
│                          [    OK    ]│
└─────────────────────────────────────┘

Colors:
- Background: Light Blue (#E3F2FD)
- Border: Blue (#2196F3)
- Icon: Blue
- Title: Dark Blue (#1565C0)
```

#### Warning (`popup_type: "warning"`)
```
┌─────────────────────────────────────┐
│  💰  Registration Fee Required       │
├─────────────────────────────────────┤
│                                     │
│  Almost there!                      │
│                                     │
│  You have submitted a loan          │
│  application, but you need to pay   │
│  the KES 300 registration fee       │
│  first.                             │
│                                     │
│  Once the registration fee is paid, │
│  our admin team will review your    │
│  application and set your loan      │
│  limit.                             │
│                                     │
│  Please complete the payment to     │
│  proceed.                           │
│                                     │
├─────────────────────────────────────┤
│  [ Cancel ]       [ Pay KES 300 Now ]│
└─────────────────────────────────────┘

Colors:
- Background: Light Orange (#FFF3E0)
- Border: Orange (#FF9800)
- Icon: Orange
- Title: Dark Orange (#E65100)
```

### Responsive Design

#### Mobile (React Native)
- Use `Alert.alert()` for simple popups
- Use custom Modal for branded popups
- Full screen overlay with centered dialog
- Large text (16-18px body, 20-24px title)
- Big buttons (min height 48px)

#### Web
- Modal dialog centered on screen
- Max width: 500px
- Padding: 24px
- Box shadow for elevation
- Backdrop with 50% opacity

---

## Complete Frontend Integration Example

### React/React Native
```jsx
// api/loanService.js
import axios from 'axios';
import { Alert } from 'react-native';

export const applyForLoan = async (loanData) => {
  try {
    const response = await axios.post('/api/loans', loanData);
    return { success: true, data: response.data };
  } catch (error) {
    const responseData = error.response?.data;

    // Check if this is a popup response
    if (responseData?.show_popup) {
      return {
        success: false,
        showPopup: true,
        popupData: responseData
      };
    }

    return {
      success: false,
      error: responseData?.message || 'An error occurred'
    };
  }
};

// components/LoanApplicationScreen.js
import React from 'react';
import { showPopup } from '../utils/popupHelper';

const LoanApplicationScreen = () => {
  const handleApply = async () => {
    setLoading(true);

    const result = await applyForLoan(formData);

    setLoading(false);

    if (result.success) {
      showSuccessToast(result.data.message);
      navigation.navigate('MyLoans');
    } else if (result.showPopup) {
      showPopup(result.popupData, navigation);
    } else {
      showErrorToast(result.error);
    }
  };

  return (
    // ... your form UI
  );
};

// utils/popupHelper.js
export const showPopup = (data, navigation) => {
  const buttons = [
    {
      text: 'OK',
      style: data.popup_type === 'warning' ? 'cancel' : 'default',
    },
  ];

  // Add action button if needed
  if (data.action_button_text) {
    buttons.unshift({
      text: data.action_button_text,
      onPress: () => handlePopupAction(data, navigation),
    });
  }

  const message = data.popup_message +
    (data.estimated_wait ? `\n\n${data.estimated_wait}` : '');

  Alert.alert(
    `${data.popup_icon} ${data.popup_title}`,
    message,
    buttons,
    { cancelable: false }
  );
};

const handlePopupAction = (data, navigation) => {
  switch (data.action_required) {
    case 'pay_registration_fee':
      navigation.navigate('RegistrationFeePayment', {
        amount: data.registration_fee_amount,
      });
      break;
    case 'wait_for_review':
      navigation.navigate('MyApplications');
      break;
    case 'wait_for_admin_approval':
      navigation.navigate('LoanStatus');
      break;
    default:
      navigation.goBack();
  }
};
```

---

## Testing Checklist

### Test Scenario 1: Second Application (Fee Paid)
- [ ] Make first loan application
- [ ] Pay registration fee (KES 300)
- [ ] Try to make second application
- [ ] Should see popup with title "Application Under Review"
- [ ] Popup should have ⏳ icon
- [ ] Message should mention 24-48 hours
- [ ] Only one "OK" button

### Test Scenario 2: Second Application (Fee Not Paid)
- [ ] Make first loan application
- [ ] Do NOT pay registration fee
- [ ] Try to make second application
- [ ] Should see popup with title "Registration Fee Required"
- [ ] Popup should have 💰 icon
- [ ] Should have "Pay KES 300 Now" button
- [ ] Clicking button navigates to payment screen

### Test Scenario 3: Deposit Payment Before Approval
- [ ] Make loan application
- [ ] Pay registration fee
- [ ] Try to pay deposit (before admin sets limit)
- [ ] Should see popup with title "Loan Under Review"
- [ ] Popup should have ⏳ icon
- [ ] Message should mention waiting for admin
- [ ] Only one "OK" button

---

## HTTP Status Codes

| Status | Meaning | Use Case |
|--------|---------|----------|
| `202 Accepted` | Request received but can't be processed yet | Waiting for admin review |
| `402 Payment Required` | Payment needed before proceeding | Registration fee not paid |
| `400 Bad Request` | Regular validation error | Use for non-popup errors |

---

## Fallback Handling

If the frontend doesn't support popups yet, the response still works as a regular error:

```jsx
// Fallback for old frontend versions
if (!data.show_popup) {
  showErrorToast(data.message);
} else {
  // Show popup with full details
  showBigPopup(data);
}
```

The `message` field contains a short version suitable for toasts.

---

## Summary

### Backend Changes
- ✅ Loan application returns popup response when credit_limit = 0
- ✅ Deposit payment returns popup response when credit_limit = 0
- ✅ Different messages based on registration fee payment status
- ✅ Proper HTTP status codes (202, 402)

### Frontend Requirements
- Display big popup dialog (not toast)
- Parse `show_popup` flag
- Handle different popup types (info, warning)
- Show action buttons when provided
- Navigate to payment screen when needed
- Display estimated wait time

### User Experience
- ❌ No more confusing error messages
- ✅ Clear, friendly explanations
- ✅ Guidance on what to do next
- ✅ Expected wait times shown
- ✅ Easy action buttons for payments

---

**Last Updated:** 2025-10-25
**API Version:** v1
**Status:** ✅ Ready for Frontend Integration
