USE ecommerce;

INSERT INTO categories (name, slug)
VALUES
('Electronics', 'electronics'),
('Gaming', 'gaming'),
('Accessories', 'accessories');

INSERT INTO products (
    category_id,
    name,
    slug,
    description,
    price,
    stock,
    image
)
VALUES
(
    1,
    'Mechanical Keyboard',
    'mechanical-keyboard',
    'RGB mechanical gaming keyboard.',
    4500,
    15,
    'keyboard.jpg'
),
(
    1,
    'Wireless Mouse',
    'wireless-mouse',
    'High precision wireless mouse.',
    2500,
    30,
    'mouse.jpg'
),
(
    2,
    'Gaming Headset',
    'gaming-headset',
    'Surround sound gaming headset.',
    6500,
    12,
    'headset.jpg'
),
(
    3,
    'USB-C Hub',
    'usb-c-hub',
    'Multiport USB-C adapter.',
    3200,
    20,
    'hub.jpg'
);