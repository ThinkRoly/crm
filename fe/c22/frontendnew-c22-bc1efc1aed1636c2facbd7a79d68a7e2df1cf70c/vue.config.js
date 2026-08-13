module.exports = {
    devServer: {
      proxy: {
        '/api': {
          target: 'http://crm.zhaoyuhao.com',
          changeOrigin: true,
          ws: true,
        }
      }
    }
  }