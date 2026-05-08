import { test, expect } from "@playwright/test";
import AxeBuilder from "@axe-core/playwright";
import { createHtmlReport } from "axe-html-reporter";
import fs from "fs";
import path from "path";

const reportDir = path.join(__dirname, "a11y-report");

async function scanPage(page: any, testInfo: any) {
  const axeResults = await new AxeBuilder({ page })
    .withTags(["wcag2a", "wcag2aa", "wcag21a", "wcag21aa"])
    // .exclude("#element-with-known-a11y-issue")
    .analyze();

  const reportHTML = createHtmlReport({
    results: axeResults,
    options: { projectKey: "unlocode.info A11Y" },
  });

  fs.mkdirSync(reportDir, { recursive: true });
  const fileName = testInfo.title.replace(/\s+/g, "-") + ".html";
  fs.writeFileSync(path.join(reportDir, fileName), reportHTML);

  return axeResults;
}

test.describe("Accessibility", () => {
  test("homepage should be accessible", async ({ page }, testInfo) => {
    await page.goto("/");
    await test.step("run accessibility scan", async () => {
      const axeResults = await scanPage(page, testInfo);
      expect(axeResults.violations.length).toBe(0);
    });
  });

  test("about page should be accessible", async ({ page }, testInfo) => {
    await page.goto("/about");
    await test.step("run accessibility scan", async () => {
      const axeResults = await scanPage(page, testInfo);
      expect(axeResults.violations.length).toBe(0);
    });
  });

  test("country page should be accessible", async ({ page }, testInfo) => {
    await page.goto("/country/NL");
    await test.step("run accessibility scan", async () => {
      const axeResults = await scanPage(page, testInfo);
      expect(axeResults.violations.length).toBe(0);
    });
  });

  test("UNLOCODE detail page should be accessible", async ({ page }, testInfo) => {
    await page.goto("/NLRTM");
    await test.step("run accessibility scan", async () => {
      const axeResults = await scanPage(page, testInfo);
      expect(axeResults.violations.length).toBe(0);
    });
  });
});
