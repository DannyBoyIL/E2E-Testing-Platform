import {request} from '@playwright/test';

async function globalTeardown() {
    const context = await request.newContext({
        baseURL: 'http://127.0.0.1:8000',
    });

    await context.post('/api/test/cleanup');
    await context.dispose();
    console.log('✓ Test data cleaned up');
}

export default globalTeardown;
