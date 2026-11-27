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

/** Current price of each item, keyed by item id. */
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

/** A kit card shown by Shared/KitGrid.vue (shop, favourites and orders). */
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

/** A line of the client-side cart, stored in localStorage under "items". */
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
