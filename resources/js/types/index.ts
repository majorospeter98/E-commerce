export interface Item {
    id: number;
    team: string;
    type: string;
    brand: string;
    quantity: string;
    size: string[];
    image: string;
    price: number;
}

export type Prices = Record<number, number>;

export interface Favourite {
    id: number;
    user_id: number;
    item_id: number;
    item: Item;
}

export interface OrderItem extends Omit<Item, "size"> {
    pivot: { size: string; quantity: number; unit_price: number | null };
}

export interface Order {
    id: number;
    created_at: string;
    items: OrderItem[];
}

export interface Kit {
    key: string | number;
    href: string;
    team: string;
    type: string;
    brand: string;
    image: string;
    price?: number;
    details?: string[];
}

export interface CartLine {
    id: string;
    item_id: number;
    image: string;
    team: string;
    type: string;
    brand: string;
    size: string;
    quantity: number;
}

export interface FormErrors {
    [field: string]: string | undefined;
}
