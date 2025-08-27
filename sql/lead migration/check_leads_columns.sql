-- Check actual columns in leads table
SELECT 'Checking leads table columns...' as status;
DESCRIBE leads;

-- Check if there are any columns that might be related to contact type
SELECT 'Looking for type-related columns in leads...' as status;
SHOW COLUMNS FROM leads LIKE '%type%';

-- Also check ctype column specifically (saw it in earlier diagnostic)
SELECT 'Checking ctype column...' as status;
SELECT 
    ctype,
    COUNT(*) as count
FROM leads 
GROUP BY ctype 
ORDER BY ctype;

-- Check contacts table ctype column too
SELECT 'Checking contacts table ctype...' as status;
SELECT 
    ctype,
    COUNT(*) as count
FROM contacts 
GROUP BY ctype 
ORDER BY ctype;