# Mautic Opportunities API - Curl Examples

## Prerequisites

Replace the following placeholders:
- `YOUR_MAUTIC_URL` - Your Mautic installation URL
- `USERNAME:PASSWORD` - Your Mautic API credentials
- `{id}` - Actual opportunity ID
- `{contact_id}` and `{event_id}` - Existing contact and event IDs

## Basic CRUD Operations

### 1. Create Opportunity (POST)

```bash
curl -X POST \
  "YOUR_MAUTIC_URL/api/opportunities" \
  -H "Content-Type: application/json" \
  -u "USERNAME:PASSWORD" \
  -d '{
    "opportunity_external_id": "OPP-001",
    "name": "Conference Registration",
    "amount": 299.99,
    "sales_stage": "Prospecting",
    "contact_id": 1,
    "event_id": 1
  }'
```

### 2. Get All Opportunities (GET)

```bash
curl -X GET \
  "YOUR_MAUTIC_URL/api/opportunities" \
  -H "Content-Type: application/json" \
  -u "USERNAME:PASSWORD"
```

### 3. Get Opportunity by ID (GET)

```bash
curl -X GET \
  "YOUR_MAUTIC_URL/api/opportunities/{id}" \
  -H "Content-Type: application/json" \
  -u "USERNAME:PASSWORD"
```

### 4. Update Opportunity (PATCH)

```bash
curl -X PATCH \
  "YOUR_MAUTIC_URL/api/opportunities/{id}" \
  -H "Content-Type: application/json" \
  -u "USERNAME:PASSWORD" \
  -d '{
    "sales_stage": "Closed Won",
    "amount": 349.99,
    "payment_status_c": "Paid"
  }'
```

### 5. Delete Opportunity (DELETE)

```bash
curl -X DELETE \
  "YOUR_MAUTIC_URL/api/opportunities/{id}" \
  -H "Content-Type: application/json" \
  -u "USERNAME:PASSWORD"
```

## Advanced Examples

### Create Opportunity with All Fields

```bash
curl -X POST \
  "YOUR_MAUTIC_URL/api/opportunities" \
  -H "Content-Type: application/json" \
  -u "USERNAME:PASSWORD" \
  -d '{
    "opportunity_external_id": "CONF-2024-001",
    "name": "International AI Conference 2024",
    "description": "Registration for keynote presentation",
    "amount": 599.99,
    "amount_usdollar": 599.99,
    "sales_stage": "Negotiation/Review",
    "opportunity_type": "New Business",
    "lead_source": "Conference",
    "date_closed": "2024-12-31",
    "probability": 80,
    "next_step": "Send acceptance letter",
    "institution_c": "MIT",
    "review_result_c": "Accepted",
    "abstract_book_send_date_c": "2024-06-01",
    "abstract_review_result_url_c": "https://conference.com/review/001",
    "abstract_book_dpublication_c": true,
    "extra_paper_c": "Extended Abstract",
    "sales_receipt_url_c": "https://payment.com/receipt/001",
    "abstract_result_send_date_c": "2024-06-05",
    "registration_type_c": "Faculty",
    "abstract_c": "AI applications in healthcare...",
    "abstract_book_information_c": "Volume 1, Pages 45-52",
    "payment_status_c": "Paid",
    "coupon_code_c": "EARLY2024",
    "abstract_result_ready_date_c": "2024-06-03",
    "paper_title_c": "AI in Healthcare: Future Perspectives",
    "sms_permission_c": true,
    "jjwg_maps_geocode_status_c": "Geocoded",
    "invoice_url_c": "https://billing.com/invoice/001",
    "presentation_type_c": "Keynote Speech",
    "invitation_letter_url_c": "https://conference.com/invitation/001",
    "withdraw_c": false,
    "keywords_c": "AI, Healthcare, Machine Learning",
    "jjwg_maps_lng_c": -71.0942,
    "jjwg_maps_lat_c": 42.3601,
    "transaction_id_c": "TXN20240001",
    "co_authors_names_c": "Dr. Smith, Prof. Johnson",
    "abstract_attachment_c": "https://files.com/abstract.pdf",
    "acceptance_letter_url_c": "https://conference.com/acceptance/001",
    "payment_channel_c": "Credit Card",
    "wire_transfer_attachment_c": "https://files.com/transfer.pdf",
    "jjwg_maps_address_c": "77 Massachusetts Ave, Cambridge, MA",
    "invitation_url": "https://conference.com/invite/001",
    "suitecrm_id": "SUITE001",
    "contact_id": 1,
    "event_id": 1
  }'
```

### Search Opportunities

```bash
# Search by name
curl -X GET \
  "YOUR_MAUTIC_URL/api/opportunities?search=Conference" \
  -H "Content-Type: application/json" \
  -u "USERNAME:PASSWORD"

# Filter by sales stage
curl -X GET \
  "YOUR_MAUTIC_URL/api/opportunities?where[0][col]=salesStage&where[0][expr]=eq&where[0][val]=Prospecting" \
  -H "Content-Type: application/json" \
  -u "USERNAME:PASSWORD"

# Filter by amount greater than 500
curl -X GET \
  "YOUR_MAUTIC_URL/api/opportunities?where[0][col]=amount&where[0][expr]=gt&where[0][val]=500" \
  -H "Content-Type: application/json" \
  -u "USERNAME:PASSWORD"
```

### Pagination

```bash
# Get opportunities with limit and start
curl -X GET \
  "YOUR_MAUTIC_URL/api/opportunities?limit=10&start=0" \
  -H "Content-Type: application/json" \
  -u "USERNAME:PASSWORD"
```

### Order Results

```bash
# Order by amount descending
curl -X GET \
  "YOUR_MAUTIC_URL/api/opportunities?orderBy=amount&orderByDir=DESC" \
  -H "Content-Type: application/json" \
  -u "USERNAME:PASSWORD"
```

## Available Field Mappings

The API accepts both camelCase and snake_case field names:

| Snake Case | Camel Case | Description |
|-----------|------------|-------------|
| `opportunity_external_id` | `opportunityExternalId` | External ID |
| `sales_stage` | `salesStage` | Sales stage |
| `opportunity_type` | `opportunityType` | Opportunity type |
| `lead_source` | `leadSource` | Lead source |
| `amount_usdollar` | `amountUsdollar` | Amount in USD |
| `date_closed` | `dateClosed` | Date closed |
| `next_step` | `nextStep` | Next step |
| `institution_c` | `institutionC` | Institution |
| `review_result_c` | `reviewResultC` | Review result |
| `payment_status_c` | `paymentStatusC` | Payment status |
| `presentation_type_c` | `presentationTypeC` | Presentation type |
| `registration_type_c` | `registrationTypeC` | Registration type |
| `paper_title_c` | `paperTitleC` | Paper title |
| `abstract_c` | `abstractC` | Abstract content |
| `keywords_c` | `keywordsC` | Keywords |
| `invoice_url_c` | `invoiceUrlC` | Invoice URL |
| `invitation_url` | `invitationUrl` | Invitation URL |
| `suitecrm_id` | `suitecrmId` | SuiteCRM ID |
| `contact_id` | `contact` | Contact ID |
| `event_id` | `event` | Event ID |

## Available Values

### Sales Stages
- Prospecting
- Qualification
- Needs Analysis
- Value Proposition
- Id. Decision Makers
- Perception Analysis
- Proposal/Price Quote
- Negotiation/Review
- Closed Won
- Closed Lost

### Opportunity Types
- Existing Business
- New Business

### Lead Sources
- Cold Call
- Existing Customer
- Self Generated
- Employee
- Partner
- Public Relations
- Direct Mail
- Conference
- Trade Show
- Web Site
- Word of mouth
- Email
- Campaign
- Other

### Registration Types
- Early Bird
- Regular
- Student
- Faculty
- Industry
- International

### Payment Statuses
- Pending
- Paid
- Failed
- Refunded
- Cancelled

### Payment Channels
- Credit Card
- Bank Transfer
- PayPal
- Wire Transfer
- Check
- Cash

### Presentation Types
- Oral Presentation
- Poster Presentation
- Keynote Speech
- Workshop
- Panel Discussion

### Review Results
- Accepted
- Rejected
- Revision Required
- Under Review