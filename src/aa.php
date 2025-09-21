<?php
/**
 * GitHub WebHook 自动拉取并构建脚本
 * 当有代码推送到GitHub仓库时，自动拉取代码并执行npm run build
 */

// 配置信息
$config = [
    'secret' => '88888888', // GitHub WebHook中设置的密钥
    'repo_path' => '/volume3/web/iweekly', // 仓库在服务器上的路径
    'npm_path' => '/volume3/@appstore/Node.js_v22/usr/local/bin/npx', // npm可执行文件路径，可能需要根据实际情况修改
    'log_file' => __DIR__ . '/iweekly-github-webhook.log' // 日志文件路径
];

// 记录日志
function logMessage($message, $logFile) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}


putenv('ASTRO_TELEMETRY_DISABLED=1');
putenv('XDG_CONFIG_HOME=' . __DIR__ . '/.config');
 if (!is_dir('.config')) {
            mkdir('.config', 0755, true);
        }
  chdir($config['repo_path']);
 $nodePath = trim(shell_exec('which node'));

 putenv('PATH=' . getenv('PATH') . ':/usr/local/bin:/opt/bin:/usr/bin');
   $buildCmd =  "/usr/local/bin/npm run build 2>&1";

                exec($buildCmd, $buildOutput, $buildReturn);
                $output .= implode("\n", $buildOutput) . "\n";

                if ($buildReturn !== 0) {
                    $output .= "Error: npm run build failed\n";
                } else {
                    $output .= "Build completed successfully!\n";
                    $success = true;
                }

 var_dump( $output);
 exit;

// 验证GitHub签名
function verifySignature($payload, $secret, $signatureHeader) {
    list($algorithm, $signature) = explode('=', $signatureHeader, 2);
    $hash = hash_hmac($algorithm, $payload, $secret);
    return hash_equals($hash, $signature);
}

try {
    // 获取请求头中的签名
    $signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

    // 读取请求体
    $payload = file_get_contents('php://input');

    // 验证签名
	/**
    if (empty($signature) || !verifySignature($payload, $config['secret'], $signature)) {
        http_response_code(403);
        logMessage("签名验证失败", $config['log_file']);
        exit("签名验证失败");
    }
    **/
    // 解析 payload 获取事件类型
    $eventType = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';
    $payloadData = json_decode($payload, true);

    logMessage("收到{$eventType}事件，开始处理", $config['log_file']);

    // 只处理push事件
   // if ($eventType === 'push') {
        $branch = $payloadData['ref'] ?? '';
        logMessage("收到来自{$branch}分支的推送", $config['log_file']);

        // 切换到仓库目录
        chdir($config['repo_path']);

        // 拉取最新代码
        $pullOutput = [];
        $pullResult = 0;

       // exec('git fetch --all && git reset --hard origin/main && git  pull origin main 2>&1', $pullOutput, $pullResult);
		//     var_dump( $pullOutput, $pullResult);

      //  if ($pullResult !== 0) {
      //      $error = implode("\n", $pullOutput);
      //      logMessage("拉取代码失败: {$error}", $config['log_file']);
      //      http_response_code(500);
      //      exit("拉取代码失败");
      //  }

        logMessage("代码拉取成功", $config['log_file']);

        // 执行npm run build
        $buildOutput = [];
        $buildResult = 0;
        exec("{$config['npm_path']} run build 2>&1", $buildOutput, $buildResult);
         var_dump( $buildOutput, $buildResult);
        if ($buildResult !== 0) {
            $error = implode("\n", $buildOutput);
            logMessage("构建失败: {$error}", $config['log_file']);
            http_response_code(500);
            exit("构建失败");
        }

        logMessage("构建成功", $config['log_file']);
        echo "操作成功完成";
    //} else {
  //      logMessage("忽略非push事件: {$eventType}", $config['log_file']);
  //      echo "忽略非push事件";
  //  }
} catch (Exception $e) {
    logMessage("发生错误: " . $e->getMessage(), $config['log_file']);
    http_response_code(500);
    exit("服务器错误");
}
?>
