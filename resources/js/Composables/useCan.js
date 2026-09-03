import { usePage } from '@inertiajs/vue3';

export function useCan() {
    const page = usePage();

    /**
     * Check if the authenticated user has a specific permission.
     * 
     * @param {string} permissionName 
     * @returns {boolean}
     */
    const can = (permissionName) => {
        const permissions = page.props.auth.permissions || [];
        return permissions.includes(permissionName);
    };

    /**
     * Check if the authenticated user has a specific role.
     * 
     * @param {string} roleName 
     * @returns {boolean}
     */
    const hasRole = (roleName) => {
        const roles = page.props.auth.roles || [];
        return roles.includes(roleName);
    };

    return {
        can,
        hasRole
    };
}
