# Laravel – Payment Upload, Processing & Daily Payout System

This project allows uploading payment CSV files, storing them on AWS S3, processing them asynchronously, converting all amounts to USD, storing validated records, and sending daily invoice summaries to customers.

---

# ✅ 1. Setup and Installation Instructions

## ✅ Step 1 — Clone the Project
```bash
git clone https://github.com/your-repo/payment-system.git
cd payment-system
```

## ✅ Step 2 — Install Dependencies
```bash
composer install
```

## ✅ Step 3 — Create Environment File
```bash
cp .env.example .env
php artisan key:generate
```

## ✅ Step 4 — Configure .env (IMPORTANT)

### ✅ App
```
APP_NAME="Payment System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
```

### ✅ Database
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### ✅ AWS S3 Storage (Required for file upload)
```
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_aws_key
AWS_SECRET_ACCESS_KEY=your_aws_secret
AWS_DEFAULT_REGION=ap-south-1
AWS_BUCKET=your_bucket_name
```

### ✅ Currency API (CurrencyLayer)
```
CURRENCYLAYER_KEY=your_currency_api_key
```

### ✅ Mail Configuration (Mailtrap or SMTP)
```
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@payments.com
MAIL_FROM_NAME="Payment System"
```

## ✅ Step 5 — Migrate Database
```bash
php artisan migrate
```

---

# ✅ 2. API Usage Examples

## ✅ Upload Payment CSV
**Endpoint:**
```
POST /api/payments/upload
```

**Headers:**
```
Content-Type: multipart/form-data
```

**Body:**
```
file: payments.csv
```

### ✅ Example Response
```json
{
  "message": "File uploaded and queued for processing.",
  "batch_id": 5,
  "s3_path": "payment_uploads/2025/01/15/unique_file.csv"
}
```

# ✅ 3. How to Run Background Jobs & Scheduled Task

## ✅ A) Start Queue Workers (Required)
This processes CSV rows & sends emails.

```bash
php artisan queue:work --queue=payments,emails --tries=3
```

Keep this running in a separate terminal window.

---

## ✅ B) Run Daily Payout Job Manually
This generates customer invoices for the day.

```bash
php artisan payouts:daily
```


# ✅ Summary

This README includes:
✅ Full installation steps  
✅ API usage example  
✅ CSV format  
✅ How to run queue workers  
✅ How to run daily scheduled task  

You can submit this README.md exactly as it is.
