export interface Item {
    id: number;
    team: string;
    type: string;
    brand: string;
    quantity: string;
    size: string[];
    image: string;
}

export interface Favourite {
    id: number;
    user_id: number;
    item_id: number;
    item: Item;
}

export interface OrderItem extends Omit<Item, "size"> {
    pivot: { size: string; quantity: number };
}

export interface Order {
    id: number;
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
