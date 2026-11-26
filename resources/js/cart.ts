import type { CartLine } from "@/types";

export const CART_STORAGE_KEY = "items";

/** Reads the cart from localStorage, tolerating missing or malformed data. */
export function readCartLines(): CartLine[] {
    try {
        const stored: unknown = JSON.parse(localStorage.getItem(CART_STORAGE_KEY) ?? "[]");
        return Array.isArray(stored) ? (stored as CartLine[]) : [];
    } catch {
        return [];
    }
}

/** crypto.randomUUID only exists on HTTPS and localhost, so fall back elsewhere. */
export function createLineId(): string {
    return crypto.randomUUID?.() ?? `${Date.now().toString(36)}-${Math.random().toString(36).slice(2)}`;
}

export function writeCartLines(lines: CartLine[]): void {
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(lines));
    window.dispatchEvent(new Event("cart-updated"));
}
