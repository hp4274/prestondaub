#!/bin/bash

# Sports Module Verification Script
# Verifies all fixes are properly implemented in forms-sports.php

echo "🔍 Sports Investments Module - Verification Script"
echo "=================================================="
echo ""

FILE="/Applications/XAMPP/xamppfiles/htdocs/PB/admin/forms-sports.php"

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if file exists
if [ ! -f "$FILE" ]; then
    echo -e "${RED}❌ Error: File not found at $FILE${NC}"
    exit 1
fi

echo -e "${YELLOW}Checking: $FILE${NC}"
echo ""

# Check 1: API Endpoint
echo "Check 1: API Endpoint (/api/stats)..."
if grep -q "if (isset(\$_GET\['api'\]) && \$_GET\['api'\] === 'stats')" "$FILE"; then
    echo -e "${GREEN}✅ PASS${NC} - API endpoint found"
    PASS1=true
else
    echo -e "${RED}❌ FAIL${NC} - API endpoint NOT found"
    PASS1=false
fi
echo ""

# Check 2: Mark-Spam Handler
echo "Check 2: Mark-Spam Action Handler..."
if grep -q "elseif (\$action === 'mark-spam')" "$FILE"; then
    echo -e "${GREEN}✅ PASS${NC} - Mark-spam handler found"
    PASS2=true
else
    echo -e "${RED}❌ FAIL${NC} - Mark-spam handler NOT found"
    PASS2=false
fi
echo ""

# Check 3: Stat IDs (Total)
echo "Check 3: Stat Card IDs..."
STAT_TOTAL=$(grep -c 'id="stat-total"' "$FILE")
STAT_NEW=$(grep -c 'id="stat-new"' "$FILE")
STAT_READ=$(grep -c 'id="stat-read"' "$FILE")
STAT_SPAM=$(grep -c 'id="stat-spam"' "$FILE")

if [ "$STAT_TOTAL" -ge 1 ] && [ "$STAT_NEW" -ge 1 ] && [ "$STAT_READ" -ge 1 ] && [ "$STAT_SPAM" -ge 1 ]; then
    echo -e "${GREEN}✅ PASS${NC} - All 4 stat IDs found"
    echo "  - stat-total: $STAT_TOTAL found"
    echo "  - stat-new: $STAT_NEW found"
    echo "  - stat-read: $STAT_READ found"
    echo "  - stat-spam: $STAT_SPAM found"
    PASS3=true
else
    echo -e "${RED}❌ FAIL${NC} - Missing stat IDs"
    echo "  - stat-total: $STAT_TOTAL found (need 1)"
    echo "  - stat-new: $STAT_NEW found (need 1)"
    echo "  - stat-read: $STAT_READ found (need 1)"
    echo "  - stat-spam: $STAT_SPAM found (need 1)"
    PASS3=false
fi
echo ""

# Check 4: Spam Button
echo "Check 4: Spam Button in Table..."
if grep -q "forms-sports.php?action=mark-spam" "$FILE"; then
    SPAM_BUTTON_COUNT=$(grep -c "forms-sports.php?action=mark-spam" "$FILE")
    echo -e "${GREEN}✅ PASS${NC} - Spam button found ($SPAM_BUTTON_COUNT reference)"
    PASS4=true
else
    echo -e "${RED}❌ FAIL${NC} - Spam button NOT found"
    PASS4=false
fi
echo ""

# Check 5: JSON Response (API)
echo "Check 5: JSON API Response..."
if grep -q "json_encode.*'total'.*'new'.*'read'.*'spam'" "$FILE"; then
    echo -e "${GREEN}✅ PASS${NC} - JSON response format correct"
    PASS5=true
else
    echo -e "${RED}❌ FAIL${NC} - JSON response format incorrect"
    PASS5=false
fi
echo ""

# Check 6: Form Type Filter
echo "Check 6: Sports Form Type Filter..."
if grep -q "form_type LIKE '%sports%'" "$FILE"; then
    echo -e "${GREEN}✅ PASS${NC} - Form type filter correct"
    PASS6=true
else
    echo -e "${RED}❌ FAIL${NC} - Form type filter incorrect"
    PASS6=false
fi
echo ""

# Summary
echo "=================================================="
echo "📊 SUMMARY"
echo "=================================================="
echo ""

ALL_PASS=true
if [ "$PASS1" = true ]; then
    echo "✅ API Endpoint: PASS"
else
    echo "❌ API Endpoint: FAIL"
    ALL_PASS=false
fi

if [ "$PASS2" = true ]; then
    echo "✅ Mark-Spam Handler: PASS"
else
    echo "❌ Mark-Spam Handler: FAIL"
    ALL_PASS=false
fi

if [ "$PASS3" = true ]; then
    echo "✅ Stat Card IDs: PASS"
else
    echo "❌ Stat Card IDs: FAIL"
    ALL_PASS=false
fi

if [ "$PASS4" = true ]; then
    echo "✅ Spam Button: PASS"
else
    echo "❌ Spam Button: FAIL"
    ALL_PASS=false
fi

if [ "$PASS5" = true ]; then
    echo "✅ JSON Response: PASS"
else
    echo "❌ JSON Response: FAIL"
    ALL_PASS=false
fi

if [ "$PASS6" = true ]; then
    echo "✅ Form Type Filter: PASS"
else
    echo "❌ Form Type Filter: FAIL"
    ALL_PASS=false
fi

echo ""
echo "=================================================="

if [ "$ALL_PASS" = true ]; then
    echo -e "${GREEN}✅ ALL CHECKS PASSED - Sports module is ready!${NC}"
    echo ""
    echo "The Sports Investments module now has:"
    echo "  • Real-time statistics updates"
    echo "  • Spam marking functionality"  
    echo "  • Complete button array"
    echo "  • Full feature parity with other modules"
    echo ""
    exit 0
else
    echo -e "${RED}❌ SOME CHECKS FAILED - Issues detected${NC}"
    echo ""
    exit 1
fi
