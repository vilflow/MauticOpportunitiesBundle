#!/bin/bash

# Mautic Opportunities API Test Script
# Make sure to replace YOUR_MAUTIC_URL, USERNAME, and PASSWORD with actual values

MAUTIC_URL="http://127.0.0.1:8000/s/"  # Replace with your Mautic URL
USERNAME="admin"                      # Replace with your Mautic username
PASSWORD="$Me2090230"                   # Replace with your Mautic password

echo "=== Mautic Opportunities API Test ==="
echo "URL: $MAUTIC_URL"
echo

# Test 1: Create a new opportunity (POST)
echo "1. Creating a new opportunity..."
curl -X POST \
  "$MAUTIC_URL/api/opportunities" \
  -H "Content-Type: application/json" \
  -u "$USERNAME:$PASSWORD" \
  -d '{
    "opportunity_external_id": "TEST-OPP-001",
    "name": "Test Conference Opportunity",
    "description": "Test opportunity for API testing",
    "amount": 299.99,
    "sales_stage": "Prospecting",
    "opportunity_type": "New Business",
    "lead_source": "Web Site",
    "date_closed": "2024-12-31",
    "probability": 50,
    "next_step": "Follow up with contact",
    "institution_c": "Test University",
    "review_result_c": "Under Review",
    "registration_type_c": "Early Bird",
    "payment_status_c": "Pending",
    "presentation_type_c": "Oral Presentation",
    "paper_title_c": "AI in Modern Healthcare",
    "abstract_c": "This paper discusses the implementation of AI in healthcare systems.",
    "keywords_c": "AI, Healthcare, Technology",
    "co_authors_names_c": "Dr. John Smith, Dr. Jane Doe",
    "coupon_code_c": "EARLY2024",
    "payment_channel_c": "Credit Card",
    "sms_permission_c": true,
    "withdraw_c": false,
    "abstract_book_dpublication_c": true,
    "contact_id": 1,
    "event_id": 1
  }' | jq '.'




# echo
# echo "2. Creating opportunity with snake_case fields..."
# curl -X POST \
#   "$MAUTIC_URL/api/opportunities" \
#   -H "Content-Type: application/json" \
#   -u "$USERNAME:$PASSWORD" \
#   -d '{
#     "opportunity_external_id": "TEST-OPP-002",
#     "name": "Test Workshop Opportunity",
#     "amount": 199.50,
#     "sales_stage": "Qualification",
#     "opportunity_type": "Existing Business",
#     "lead_source": "Conference",
#     "institution_c": "Tech Institute",
#     "payment_status_c": "Paid",
#     "presentation_type_c": "Workshop",
#     "jjwg_maps_address_c": "123 Main St, New York, NY",
#     "jjwg_maps_lat_c": 40.7128,
#     "jjwg_maps_lng_c": -74.0060,
#     "transaction_id_c": "TXN123456",
#     "invoice_url_c": "https://example.com/invoice/123",
#     "invitation_url": "https://example.com/invite/456",
#     "contact_id": 1,
#     "event_id": 1
#   }' | jq '.'

# echo
# echo "3. Getting all opportunities (GET)..."
# curl -X GET \
#   "$MAUTIC_URL/api/opportunities" \
#   -H "Content-Type: application/json" \
#   -u "$USERNAME:$PASSWORD" | jq '.'

# echo
# echo "4. Getting opportunity by ID (GET)..."
# # Replace {id} with actual opportunity ID from previous responses
# OPPORTUNITY_ID=1  # Replace with actual ID
# curl -X GET \
#   "$MAUTIC_URL/api/opportunities/$OPPORTUNITY_ID" \
#   -H "Content-Type: application/json" \
#   -u "$USERNAME:$PASSWORD" | jq '.'

# echo
# echo "5. Updating an opportunity (PATCH)..."
# curl -X PATCH \
#   "$MAUTIC_URL/api/opportunities/$OPPORTUNITY_ID" \
#   -H "Content-Type: application/json" \
#   -u "$USERNAME:$PASSWORD" \
#   -d '{
#     "sales_stage": "Negotiation/Review",
#     "amount": 349.99,
#     "probability": 75,
#     "payment_status_c": "Paid",
#     "review_result_c": "Accepted",
#     "abstract_review_result_url_c": "https://example.com/review-result/123",
#     "acceptance_letter_url_c": "https://example.com/acceptance/123"
#   }' | jq '.'

# echo
# echo "6. Getting opportunities with search (GET with query)..."
# curl -X GET \
#   "$MAUTIC_URL/api/opportunities?search=Test" \
#   -H "Content-Type: application/json" \
#   -u "$USERNAME:$PASSWORD" | jq '.'

# echo
# echo "7. Getting opportunities with filters (GET with where parameter)..."
# curl -X GET \
#   "$MAUTIC_URL/api/opportunities?where%5B0%5D%5Bcol%5D=salesStage&where%5B0%5D%5Bexpr%5D=eq&where%5B0%5D%5Bval%5D=Prospecting" \
#   -H "Content-Type: application/json" \
#   -u "$USERNAME:$PASSWORD" | jq '.'

# echo
# echo "8. Testing with all custom fields..."
# curl -X POST \
#   "$MAUTIC_URL/api/opportunities" \
#   -H "Content-Type: application/json" \
#   -u "$USERNAME:$PASSWORD" \
#   -d '{
#     "opportunity_external_id": "TEST-OPP-FULL-003",
#     "name": "Complete Test Opportunity",
#     "description": "Opportunity with all possible fields",
#     "amount": 999.99,
#     "amount_usdollar": 999.99,
#     "sales_stage": "Closed Won",
#     "opportunity_type": "New Business",
#     "lead_source": "Email",
#     "date_closed": "2024-06-30",
#     "probability": 100,
#     "next_step": "Complete registration process",
#     "institution_c": "Advanced Research Institute",
#     "review_result_c": "Accepted",
#     "abstract_book_send_date_c": "2024-05-15",
#     "abstract_review_result_url_c": "https://example.com/review/full-test",
#     "abstract_book_dpublication_c": true,
#     "extra_paper_c": "Additional Research Paper",
#     "sales_receipt_url_c": "https://example.com/receipt/full-test",
#     "abstract_result_send_date_c": "2024-05-20",
#     "registration_type_c": "Faculty",
#     "abstract_c": "Comprehensive research on advanced AI algorithms and their practical applications in various industries.",
#     "abstract_book_information_c": "Page 45-52 in Conference Proceedings Volume 3",
#     "payment_status_c": "Paid",
#     "coupon_code_c": "FACULTY2024",
#     "abstract_result_ready_date_c": "2024-05-18",
#     "paper_title_c": "Advanced AI Algorithms for Industry Applications",
#     "sms_permission_c": true,
#     "jjwg_maps_geocode_status_c": "Geocoded",
#     "invoice_url_c": "https://example.com/invoice/full-test",
#     "presentation_type_c": "Keynote Speech",
#     "invitation_letter_url_c": "https://example.com/invitation/full-test",
#     "withdraw_c": false,
#     "keywords_c": "AI, Machine Learning, Industry 4.0, Automation, Research",
#     "jjwg_maps_lng_c": -122.4194,
#     "jjwg_maps_lat_c": 37.7749,
#     "transaction_id_c": "TXN789012345",
#     "co_authors_names_c": "Dr. Alice Johnson, Prof. Bob Wilson, Dr. Carol Davis",
#     "abstract_attachment_c": "https://example.com/attachments/abstract-full-test.pdf",
#     "acceptance_letter_url_c": "https://example.com/acceptance/full-test",
#     "payment_channel_c": "Bank Transfer",
#     "wire_transfer_attachment_c": "https://example.com/wire-transfer/full-test.pdf",
#     "jjwg_maps_address_c": "1 Infinite Loop, Cupertino, CA 95014",
#     "invitation_url": "https://example.com/invite/full-test",
#     "suitecrm_id": "SUITE-12345-FULL",
#     "contact_id": 62,
#     "event_id": 20
#   }' | jq '.'

# echo
# echo "9. Delete an opportunity (DELETE)..."
# # Replace with actual opportunity ID
# DELETE_ID=2  # Replace with actual ID you want to delete
# curl -X DELETE \
#   "$MAUTIC_URL/api/opportunities/$DELETE_ID" \
#   -H "Content-Type: application/json" \
#   -u "$USERNAME:$PASSWORD" | jq '.'

# echo
# echo "=== API Test Complete ==="
# echo "Remember to:"
# echo "1. Replace MAUTIC_URL, USERNAME, PASSWORD with actual values"
# echo "2. Replace contact_id and event_id with existing entity IDs"
# echo "3. Replace opportunity IDs in GET, PATCH, and DELETE requests with actual IDs"
# echo "4. Install jq for JSON formatting or remove '| jq \".\"' from commands"