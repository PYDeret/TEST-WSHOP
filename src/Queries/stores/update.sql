UPDATE stores
SET name        = :name,
    address     = :address,
    city        = :city,
    postal_code = :postal_code,
    country     = :country,
    phone       = :phone,
    email       = :email,
    category    = :category,
    is_active   = :is_active
WHERE id = :id
