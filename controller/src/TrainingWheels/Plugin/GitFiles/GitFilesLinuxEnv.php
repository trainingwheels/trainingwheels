<?php

namespace TrainingWheels\Plugin\GitFiles;

class GitFilesLinuxEnv {

  public function mixinLinuxEnv($env) {
    $conn = $env->getConn();

    /**
     * Clone the repo for a user.
     */
    $env->gitRepoClone = function($user, $repo, $target, $branch) use ($conn) {
      $conn->exec_eq("git clone -q --branch $branch $repo $target");
      $conn->exec_eq("chown -R $user: $target");
    };

    /**
     * Get the current branch of a git repo.
     */
    $env->gitBranchGet = function($dir) use ($conn) {
      $git_path_opts = "--work-tree=$dir --git-dir=$dir/.git";
      return $conn->exec_get("git $git_path_opts rev-parse --abbrev-ref HEAD");
    };

    /**
     * Check if there are local changes.
     */
    $env->gitLocalChanges = function($dir) use ($conn) {
      $git_path_opts = "--work-tree=$dir --git-dir=$dir/.git";
      return $conn->exec_get("git $git_path_opts status -s");
    };

    /**
     * Check what the remote is.
     */
    $env->gitRemote = function($dir) use ($conn) {
      $git_path_opts = "--work-tree=$dir --git-dir=$dir/.git";
      return $conn->exec_get("git $git_path_opts remote -v");
    };
  }
}
