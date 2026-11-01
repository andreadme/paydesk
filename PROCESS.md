# PayDesk -- Technical Design and Development Process

## 1. Project Overview

PayDesk is a web-based product purchasing and payment portal built with
CodeIgniter 3, PHP 7.3, and MySQL 8. It supports two user roles:

### Admin

-   Can create users and products.
-   Can view all invoices, receipts, and transaction logs from Stripe
    payments.

### Non-admin / Customer

-   Can log in using assigned credentials.
-   Can browse and purchase multiple products in one order.
-   Can view their own invoices, receipts, orders, and payment status.

## 2. Technology Stack

  Component              Technology
  ---------------------- ------------------------------------
  Programming Language   PHP 7.3.33
  Backend Framework      CodeIgniter 3.1.13
  Database               MySQL 8.0.46
  Web Server             Nginx
  PHP Execution          PHP-FPM
  Payment Gateway        Stripe Checkout
  Frontend               HTML, CSS, Bootstrap 5, JavaScript
  API Format             REST/JSON
  Version Control        Git

## 3. Application Architecture

The application uses a modular monolithic architecture.

### Modules

1.  **Authentication:** Login, logout, and session management.
2.  **Users:** Customer and admin account management.
3.  **Products:** Product creation, editing, listing, and availability.
4.  **Orders:** Shopping cart, order creation, and order item
    management.
5.  **Payments:** Stripe Checkout session creation and payment
    confirmation.
6.  **Invoices:** Invoice generation and customer/admin invoice access.
7.  **Receipts:** Receipt generation after successful payment.
8.  **API:** Authentication and invoice/receipt retrieval endpoints.

## 4. User Registration and Access Control

-   Admin creates customer accounts through the admin panel.
-   Each customer has a unique email address.
-   Passwords are securely hashed using PHP's `password_hash()`.
-   Customers log in using their assigned credentials.
-   Admin accounts are created through a controlled database seeding or
    setup process.
-   Customers cannot assign themselves an admin role.
-   Role-based authorization restricts access to admin and customer
    features.
-   Customers can only access their own orders, invoices, and receipts.

## 5. Defining Workflows

### 5.1 Product Purchasing and Order Workflow

**Expected workflow:**

1.  The customer logs in.
2.  The customer browses available products.
3.  The customer selects products and quantities.
4.  The system validates product availability and prices using database
    records.
5.  The server calculates the order total.
6.  The system creates an order with pending-payment status.
7.  The system stores each selected product as an order item.
8.  The customer proceeds to Stripe Checkout.
9.  After payment confirmation, the order is marked as paid.

**Notes:** - Store a snapshot of the product name and unit price at the
time of purchase for historical accuracy. - The server must calculate
all prices and totals.

### 5.2 Payment Workflow -- Stripe

**Expected workflow:**

1.  The server creates or validates the pending order.
2.  The server creates a Stripe Checkout Session using the order's
    verified items and total.
3.  The customer is redirected to Stripe's hosted checkout page.
4.  Stripe processes the payment.
5.  Stripe redirects the customer to the configured success or
    cancellation URL.
6.  Stripe sends a webhook event to PayDesk.
7.  PayDesk verifies the webhook signature and processes the event.
8.  The system updates the payment and order status after confirming a
    successful payment.

**Notes:** 1. Stripe secret keys **must** be stored outside source
control. 2. Card details should not be stored. 3. Webhook signatures
**must** be verified. 4. Webhook event IDs must be recorded to prevent
duplicate processing. 5. Payment amounts and currency **must** be
checked against the original order. 6. Payment updates and receipt
creation **must** be handled safely within database transactions.

## 6. Invoice and Receipt Lifecycle

### Invoice

-   An invoice is generated when a valid order is created.
-   It contains a unique invoice number, customer information, order
    reference, item descriptions, quantities, prices, and total.
-   The invoice is associated with one order.
-   Customers can view their own invoices.
-   Admins can view all invoices.

### Receipts

-   A receipt is generated only after successful payment has been
    confirmed by the server.
-   It contains a unique receipt number, payment reference, payment
    date, and amount paid.
-   A receipt is associated with the successful payment.
-   Customers can view their own receipts.
-   Admins can view all receipts.

**Notes:** - Invoices and receipts are rendered as printable web pages.

**Improvement:** - PDF generation.

## 7. Designing the Database

### Tables

  -----------------------------------------------------------------------
  Table                               Description
  ----------------------------------- -----------------------------------
  `users`                             Customer and admin accounts

  `products`                          Products for purchase

  `orders`                            Customer purchase records

  `order_items`                       Products and quantities belonging
                                      to an order, including historical
                                      price snapshots

  `payments`                          Payment attempts and current
                                      payment status

  `payment_logs`                      Payment lifecycle history for admin
                                      view

  `invoices`                          Invoice records associated with
                                      orders

  `receipts`                          Receipt records associated with
                                      successful payments

  `webhook_events`                    Stripe webhook receipt and
                                      processing history

  `api_tokens`                        Hashed API authentication tokens
                                      with expiration and revocation
                                      information
  -----------------------------------------------------------------------

**Notes:** - Use primary keys (PKs) and foreign keys (FKs) to enforce
relationships. - Use unique constraints for email addresses, invoice
numbers, receipt numbers, and Stripe identifiers. - Use `DECIMAL` for
monetary values. - Store timestamps for important business events in
UTC. - Use transactions for operations that must succeed or fail
together. - Use parameterized database queries or CodeIgniter Query
Builder.

## 8. API Design

  -------------------------------------------------------------------------
  Method                  Endpoint                  Purpose
  ----------------------- ------------------------- -----------------------
  POST                    `/api/v1/auth/login`      Authenticate a customer

  POST                    `/api/v1/auth/logout`     Revoke the current API
                                                    token

  GET                     `/api/v1/invoices`        Retrieve the
                                                    authenticated
                                                    customer's invoices

  GET                     `/api/v1/invoices/{id}`   Retrieve one invoice

  GET                     `/api/v1/receipts`        Retrieve the
                                                    authenticated
                                                    customer's receipts

  GET                     `/api/v1/receipts/{id}`   Retrieve one receipt
  -------------------------------------------------------------------------

**Notes:** - Apply authentication and authorization to API endpoints. -
API authentication uses bearer tokens. - Tokens are stored as hashes,
have expiration dates, and can be revoked.

## 9. Other Security Considerations

-   Regenerate session IDs after authentication.
-   Apply role-based authorization.
-   Verify record ownership on every customer-facing order, invoice, and
    receipt request.
-   Validate and sanitize user input.
-   Use CodeIgniter's CSRF protection for applicable web forms.
-   Escape output to reduce cross-site scripting risks.
-   Use parameterized database queries or CodeIgniter Query Builder.

## 10. Transaction Management and Error Handling

-   Validate the order before initiating payment.
-   Use database transactions for related database changes.
-   Process Stripe webhook events idempotently.
-   Do not create duplicate receipts when Stripe retries a webhook.
-   Record meaningful application errors without exposing secrets or
    sensitive information.
-   Provide clear user-facing messages for failed or cancelled payments.
-   Keep payment status changes traceable.

## 11. Testing Strategy

### Authentication

-   Login with valid and invalid credentials.
-   Role restrictions and session behavior.

### Products and Orders

-   Admin product creation and updates.
-   Product availability and price validation.
-   Multiple products and quantities in one order.
-   Correct order total calculation.

### Payments and Documents

-   Successful Stripe test payment.
-   Cancelled or failed payment.
-   Invalid webhook signature.
-   Duplicate webhook event handling.
-   Invoice generation.
-   Receipt generation only after confirmed payment.

### Authorization and API

-   Customer access to their own invoices and receipts.
-   Prevention of access to another customer's records.
-   API authentication, token expiration, and revocation.
-   Invalid or unauthorized API requests.

## 12. Deployment Architecture

### Local

-   Uses WSL Ubuntu, Nginx, PHP 7.3-FPM, and MySQL 8.

### Staging

-   Should use a separate application environment and database, HTTPS,
    and Stripe test credentials.

### Production

-   Should use HTTPS, production Stripe credentials, restricted database
    access, backups, logging, and secure secret management.
-   Can begin with a single application server and database. A managed
    database or separate application and database servers can be
    introduced as availability, traffic, and operational requirements
    increase.

## 13. Assumptions and Trade-offs

-   Admin creates the customer accounts; admin accounts are provisioned
    separately.
-   A customer can purchase multiple products in one order.
-   The initial payment method is immediate card payment through Stripe
    Checkout.
-   Invoices are created when orders are created; receipts are created
    only after successful payment confirmation.
-   The application is designed as a web portal with API support. A
    future mobile application can consume the API without requiring a
    separate backend.
-   CodeIgniter 3 is retained because it is required by the assessment.
    Its legacy architecture and limited built-in API tooling should be
    considered when planning future modernization.

## 14. Development Principles

-   Development will prioritize correctness, security, and
    maintainability over unnecessary complexity.
